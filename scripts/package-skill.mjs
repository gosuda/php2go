import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const repository = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const skillsRoot = path.join(repository, 'skills');
const linkPattern = /\[[^\]\r\n]+\]\(([^)\r\n]+)\)/g;

function within(parent, child) {
  const relative = path.relative(parent, child);
  return relative === '' || (!path.isAbsolute(relative) && relative !== '..'
    && !relative.startsWith(`..${path.sep}`));
}

function localLink(target, source) {
  if (/^(?:[a-z][a-z0-9+.-]*:|#)/i.test(target)) return null;
  const hash = target.indexOf('#');
  const filename = hash < 0 ? target : target.slice(0, hash);
  const fragment = hash < 0 ? '' : target.slice(hash);
  const resolved = path.resolve(path.dirname(source), decodeURIComponent(filename));
  if (!within(skillsRoot, resolved)) {
    throw new Error(`Local link leaves skills/: ${source} -> ${target}`);
  }
  // Fail rather than silently shipping a link to an external symlink or directory.
  const real = fs.realpathSync(resolved);
  if (!within(skillsRoot, real) || fs.lstatSync(resolved).isSymbolicLink()
    || !fs.statSync(resolved).isFile()) {
    throw new Error(`Expected a regular Skill resource: ${resolved}`);
  }
  return { source: resolved, fragment };
}

function packageSkill(name, outputArgument) {
  if (!/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(name ?? '') || !outputArgument) {
    throw new Error('Usage: node scripts/package-skill.mjs <skill-name> <new-output-directory>');
  }
  const sourceRoot = path.join(skillsRoot, name);
  if (!fs.existsSync(path.join(sourceRoot, 'SKILL.md'))) {
    throw new Error(`Unknown Skill: ${name}`);
  }
  const output = path.resolve(outputArgument);
  if (within(output, repository) || within(skillsRoot, output) || fs.existsSync(output)) {
    throw new Error('Output must be a new directory outside skills/ and cannot contain the repository.');
  }

  const resources = new Map();
  function include(source) {
    if (resources.has(source)) return;
    const destination = within(sourceRoot, source)
      ? path.relative(sourceRoot, source)
      : path.join('references', 'shared', path.relative(skillsRoot, source));
    resources.set(source, destination);
  }
  function walk(directory) {
    for (const entry of fs.readdirSync(directory, { withFileTypes: true })) {
      const source = path.join(directory, entry.name);
      if (entry.isSymbolicLink()) throw new Error(`Symlink resources are not supported: ${source}`);
      if (entry.isDirectory()) walk(source);
      else if (entry.isFile()) include(source);
      else throw new Error(`Unsupported resource: ${source}`);
    }
  }
  walk(sourceRoot);

  // Map iteration visits added resources too; the Map also terminates reference cycles.
  const contents = new Map();
  for (const [source] of resources) {
    const data = fs.readFileSync(source);
    contents.set(source, data);
    if (!source.endsWith('.md')) continue;
    for (const match of data.toString('utf8').matchAll(linkPattern)) {
      const dependency = localLink(match[1], source);
      if (dependency) include(dependency.source);
    }
  }

  // Resolve and rewrite everything before creating any output files.
  const generated = new Map();
  for (const [source, destination] of resources) {
    let data = contents.get(source);
    if (source.endsWith('.md')) {
      data = data.toString('utf8').replace(linkPattern, (match, target) => {
        const dependency = localLink(target, source);
        if (!dependency) return match;
        const rewritten = path.relative(path.dirname(destination), resources.get(dependency.source))
          .split(path.sep).join('/') + dependency.fragment;
        return match.replace(`](${target})`, `](${rewritten})`);
      });
    }
    generated.set(destination, data);
  }
  if (generated.has('LICENSE')) throw new Error('Skill resource collides with packaged LICENSE.');
  generated.set('LICENSE', fs.readFileSync(path.join(repository, 'LICENSE')));
  fs.mkdirSync(path.dirname(output), { recursive: true });
  fs.mkdirSync(output);
  for (const [destination, data] of generated) {
    const target = path.join(output, destination);
    fs.mkdirSync(path.dirname(target), { recursive: true });
    fs.writeFileSync(target, data, { flag: 'wx' });
  }
  process.stdout.write(`Packaged ${name}: ${resources.size} resources and LICENSE -> ${output}\n`);
}

try {
  if (process.argv.length !== 4) throw new Error('Expected a Skill name and a new output directory.');
  packageSkill(process.argv[2], process.argv[3]);
} catch (error) {
  process.stderr.write(`${error.message}\n`);
  process.exitCode = 1;
}
