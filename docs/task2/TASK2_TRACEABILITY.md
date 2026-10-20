# Task 2 Pipeline Traceability

The pipeline provides evidence for existing Azure Boards items. It does not complete those items automatically; update their states only after the team's acceptance criteria are met.

| Azure ID | Area | Pipeline relationship | Evidence | Evidence status |
|---|---|---|---|---|
| #34 | DevOps tracking | Pipeline YAML, GitHub authorization, triggers, and CI lifecycle evidence | `azure-pipelines.yml`; Azure run pending administrator setup | In Progress; Azure connection BLOCKED |
| #35 | Development | PHP lint and Android build validate code changes | Local PHP suite and Android build passed; Azure validation pending | In Progress |
| #37 | Testing | Runs the existing PHP automated suite and propagates failure | Local result: 20 passed / 0 failed; temporary failure returned exit 1; Azure run pending | Local PASS; Azure run BLOCKED |
| #38 | Documentation | Records pipeline behavior, use, evidence, and limits | `AZURE_PIPELINE.md`, `DEVOPS_EVIDENCE.md`, this traceability table | In Progress; Azure run evidence pending |
| #39 | Existing prototype | Android debug APK compilation checks the existing Capacitor project | Local Gradle build passed; no device UI test or Azure build yet | Local build PASS; Azure build BLOCKED |

## Work-item linking

Use `AB#34` for pipeline implementation and `AB#37` when a commit or pull request is specifically about test execution. Use `AB#35`, `AB#38`, or `AB#39` only when the changes directly support those items. Azure Boards/GitHub linkage does not substitute for reviewing the pipeline run or its test output.
