# php2go

**Migrate PHP to Go with AI, without breaking existing behavior.**

php2go는 오래된 PHP 서비스를 Go로 마이그레이션하기 위한 **Agent Skills 프로젝트**입니다.

AI로 코드 변환을 가속하면서도, 기존 PHP와 Go를 같은 조건에서 실행해 실제 동작이 일치하는지 검증하는 것을 목표로 합니다.

## Why Go?

PHP는 오랫동안 웹 서비스를 지탱해 왔지만, 레거시 시스템에서는 오래된 런타임, 확장 모듈, 복잡한 배포 환경이 유지보수 부담으로 이어지곤 합니다. 최근 발견된 PHP 런타임 보안 취약점 역시 지속적인 업데이트와 운영 관리의 중요성을 보여줍니다.

Go는 이러한 서비스를 더 단순한 구조로 옮기기에 좋은 선택입니다.

- **Simple deployment** — 별도 PHP 런타임 없이 컴파일된 실행 파일을 배포할 수 있습니다.
- **Maintainable code** — 정적 타입, 명시적인 오류 처리, 단순한 패키지 구조로 코드를 정리할 수 있습니다.
- **Built for servers** — HTTP, SQL, 동시성 처리와 테스트 도구를 기본적으로 제공합니다.
- **SQL-first** — 기존 SQL을 보존하고 타입 안전한 데이터 접근 계층을 구성할 수 있습니다.

물론 Go로 옮긴다고 모든 보안 문제가 자동으로 해결되는 것은 아닙니다. 중요한 것은 **이해하고 검증할 수 있는 코드로 전환하는 것**입니다.

## How it works

AI가 PHP 코드를 Go로 번역하는 것만으로는 충분하지 않습니다. HTTP 응답이 같더라도 DB 변경, 세션, 파일 생성 등의 동작이 달라질 수 있기 때문입니다.

php2go는 다음 과정을 지향합니다.

1. **Analyze** — 기존 PHP의 동작과 의존성을 분석합니다.
2. **Contract** — 기능별 Behavior Contract와 재현 가능한 입력을 만듭니다.
3. **Migrate** — 기존 URL, SQL, DB 구조를 최대한 유지하며 Go로 구현합니다.
4. **Verify** — PHP와 Go를 실행해 응답과 부작용을 비교합니다.

원본의 보안상 위험한 동작을 수정해야 한다면, 해당 차이를 의도적인 변경으로 별도 기록합니다.

## Current status

현재 PHP 분석, 계약 추출, fixture 수집, SQL 추출, Go 구현, 동등성 검증, 보안 검토를 위한 **7개 Agent Skill**을 제공합니다.

검증 CLI와 자동 수집·추출 도구는 아직 개발 전입니다.

자세한 작업 지침은 [AGENTS.md](AGENTS.md)와 [skills/](skills/)에서 확인할 수 있습니다.

[MIT License](LICENSE)