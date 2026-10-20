# FSMS Azure Pipeline

## Purpose

`azure-pipelines.yml` adds continuous integration for the existing FSMS GitHub repository. It validates the PHP code, runs the existing automated test suite, builds the Android debug APK, and publishes that APK as a build artifact. It does not deploy the application.

GitHub remains the source repository. Azure Boards holds planning and work-item links; Azure Pipelines checks out the connected GitHub repository and reports build results. Use references such as `AB#34` and `AB#37` in commit messages or pull request descriptions where appropriate.

## Triggers

- Pushes to `main`, `feature/*`, `backend/*`, `frontend/*`, and `shared/*`.
- Pull requests targeting `main`.

## Jobs and commands

| Job | Environment | Checks |
|---|---|---|
| `PhpValidation` | Microsoft-hosted `ubuntu-24.04`; installs PHP CLI and `pdo_sqlite` with `apt` | `git ls-files -z -- '*.php' \| xargs -0 -r -n 1 php -l`; `php tests/run_all_tests.php` with `FSMS_TEST_SQLITE=1` |
| `AndroidDebugBuild` | Microsoft-hosted `ubuntu-24.04`, Node.js 20, Java and Android SDK | `npm ci`, then `cd android && ./gradlew assembleDebug`; verifies and publishes `android/app/build/outputs/apk/debug/app-debug.apk` |

The PHP suite runs against in-memory SQLite so it does not require MySQL or alter a shared database. The runner exits nonzero if an assertion or test errors. The Android job compiles the checked-in Capacitor Android project; it does not launch an emulator or perform device UI tests.

## Dependency and scope decisions

- There is no `composer.json`, so Composer installation is not applicable.
- `package.json` has no frontend build or test script. The Android build still requires `@capacitor/android`; the pipeline installs the pinned npm dependency tree with `npm ci` from the tracked `package-lock.json` before Gradle resolves the local Capacitor Android project.
- PHP hosted-agent dependencies are checked before lint/tests. Gradle uses the checked-in wrapper to select Gradle 8.7 and resolves Android dependencies during the build.
- No MySQL service, deployment target, Azure service connection, signing key, or production credential is needed for these CI checks.
- The pipeline builds the Android package but does not synchronize Capacitor assets. When web assets change, run `npx cap sync android` and include the resulting Android asset changes in the same PR.

## Azure DevOps setup

The YAML is prepared but has not yet run in Azure DevOps. A project administrator must:

1. In Azure DevOps, select **Pipelines > New pipeline**.
2. Choose **GitHub** and authorize Azure Pipelines for the existing FSMS repository. Keep GitHub as the source; do not import or migrate it to Azure Repos.
3. Select **Existing Azure Pipelines YAML file** and choose `/azure-pipelines.yml` on the `feature/34-azure-ci-pipeline` branch (or after that branch is merged).
4. Save and run the pipeline, authorizing repository access if prompted.
5. Check that a push and a pull request to `main` produce runs and that a PHP test failure marks the run failed.
6. Save the successful and failed-run URLs or screenshots in the project evidence folder after they actually occur.

Until those steps are completed, Azure pipeline connection and execution evidence are **BLOCKED**. The GitHub Actions workflow at `.github/workflows/ci.yml` is separate and remains unchanged.
