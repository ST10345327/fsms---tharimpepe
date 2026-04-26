# Contributing Guide

This repository should be reviewed against the academic project documents first, then against the code.

Primary references:
- [Task2a_Requirements_Analysis.docx](/C:/Users/CASH/Desktop/fsms%20-%20tharimpepe/Documentation/Task2a_Requirements_Analysis.docx)
- [Task2b_System_Design.docx](/C:/Users/CASH/Desktop/fsms%20-%20tharimpepe/Documentation/Task2b_System_Design.docx)
- [Figma_Prototype_Handoff.md](/C:/Users/CASH/Desktop/fsms%20-%20tharimpepe/Documentation/Figma_Prototype_Handoff.md)

## Phase-Based Development

Use one of these labels for each branch and pull request:
- `backend`
- `frontend`
- `shared`

### Backend PR examples

- `backend/beneficiary-validation`
- `backend/attendance-meal-session-alignment`
- `backend/report-query-cleanup`

### Frontend PR examples

- `frontend/dashboard-shell`
- `frontend/attendance-screen-polish`
- `frontend/reports-layout-alignment`

### Shared PR examples

- `shared/ci-and-review-workflow`
- `shared/repo-docs-cleanup`

## Commit Format

Use short reviewer-friendly commit prefixes:
- `backend:`
- `frontend:`
- `shared:`

Examples:
- `backend: add duplicate username validation coverage`
- `frontend: align dashboard cards with prototype handoff`
- `shared: add php ci workflow and pr template`

## Pull Request Rules

Each PR should:
- focus on one phase or one shared workflow topic
- cite the documented module or workflow it changes
- list the files or modules touched
- explain how the change was tested
- keep screenshots for frontend changes when helpful

## Reviewer Checklist

- Does the change map back to the documented FSMS scope?
- Is it clearly backend, frontend, or shared?
- Are commits small and readable?
- Does CI pass?
- Is the verification note clear?
- Are unrelated files left out of the PR?

## CI Expectation

Every development PR should expect CI to run:
- PHP syntax checks
- test suite execution

If CI is red, the PR is not reviewer-ready.
