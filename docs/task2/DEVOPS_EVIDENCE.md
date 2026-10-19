# Task 2 DevOps Evidence Log

This log records observed local results on 2026-10-02 and Azure Pipeline work still pending. No Azure run screenshots or results are claimed here.

## Local verification

| Check | Result | Evidence |
|---|---|---|
| `php tests/run_all_tests.php` | PASS | 20 tests passed, 0 failed, 37 assertions, using the in-memory SQLite fixtures. |
| Failure-to-exit behavior | PASS | A temporary failing test was added, the suite reported 20 pass / 1 fail and returned exit code 1; the temporary file was removed afterward. |
| PHP syntax check | PASS (local) | `php -l` reported no syntax errors for every tracked PHP file. The Azure-hosted run is still pending. |
| Android debug build | PASS (local) | `android\gradlew.bat assembleDebug` succeeded locally. No Android device or emulator was connected for runtime UI validation. |

The first audit run of the PHP suite against the machine's configured MySQL database produced 16 passes and 4 failures because model-test fixtures use SQLite DDL. The suite now runs its documented CLI command against isolated SQLite by default; its fixtures were completed to match the model columns used by those tests.

## Azure execution evidence

| Evidence item | Status | Record |
|---|---|---|
| Azure DevOps GitHub repository authorization | BLOCKED | Requires an Azure project administrator to authorize the existing GitHub repository for Azure Pipelines. |
| Azure pipeline created from `azure-pipelines.yml` | BLOCKED | YAML is in the repository branch; no Azure pipeline definition has been created from this session. |
| Successful Azure CI run | BLOCKED | Capture run URL, commit, date, job results, and artifact link after the first actual run. |
| Failed validation run | BLOCKED | The local failure-path probe confirms the PHP runner returns nonzero. Confirm Azure marks the job/run failed using a safe temporary branch change or a naturally failing change; do not damage `main`. |
| Android APK pipeline artifact | BLOCKED | Confirm artifact publication on an actual successful Azure run. |

## Limitations

- No Azure Pipeline run has occurred, so hosted-agent setup, GitHub authorization, trigger behavior, and artifact publication remain unverified.
- The Android job compiles a debug APK; it does not run instrumentation/UI tests because those require an emulator/device. Azure's Microsoft-hosted Ubuntu agent does not provide hardware acceleration for the Android emulator.
- This CI configuration does not deploy or publish to a store. It provides build/test evidence only.
