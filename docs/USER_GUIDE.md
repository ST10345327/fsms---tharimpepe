# Tharimpepe Feeding Scheme Management System

## User Guide

This guide covers the FSMS website and Android app. The screens and actions available to you depend on your account role. The Android app and website use the same server data when connected to the configured API.

## Contents

1. [Getting started](#getting-started)
2. [Sign in and account access](#sign-in-and-account-access)
3. [Dashboard and navigation](#dashboard-and-navigation)
4. [Beneficiaries](#beneficiaries)
5. [Attendance](#attendance)
6. [Food stock](#food-stock)
7. [Donations](#donations)
8. [Volunteers and schedules](#volunteers-and-schedules)
9. [Reports](#reports)
10. [Users and profile](#users-and-profile)
11. [Troubleshooting and privacy](#troubleshooting-and-privacy)

## Getting started

For local setup and installation, follow [GETTING_STARTED.md](../GETTING_STARTED.md). The system needs a running PHP application/API and configured database. On Android, the device must be able to reach the API address configured for that build.

Open the website URL provided by your administrator, or launch the installed FSMS Android app. Use an account supplied or approved by the administrator. Sign out when finished, especially on a shared device.

## Sign in and account access

Enter your username and password on the sign-in screen and select **Login**. If you do not have an account, use **Create one** where available or ask the administrator to create an account. Registration options can vary between the website and Android app; a newly registered account may need approval or activation before it can sign in.

The sign-in screen also offers **Quick Donate as Guest** for a one-time donation without signing in. This does not create a staff or volunteer account.

If you cannot sign in, check your username and password and ask the administrator to confirm that your account is active. The app does not show a third-party Google or Microsoft sign-in option.

## Dashboard and navigation

After signing in, use the dashboard to review the operational summaries available to your role. Use the website sidebar or Android navigation drawer/bottom navigation to open modules. On smaller website screens, open the menu with the menu button.

Available modules include beneficiaries, attendance, food stock, donations, volunteers, reports, and profile. User administration is restricted to administrators. Some actions are further restricted by role.

## Beneficiaries

Open **Beneficiaries** to view and search records. Use the registration form to add a beneficiary, enter the required details, and save. Open a record to review or update it if your role permits. Enter accurate information and collect only details needed by the feeding scheme.

## Attendance

Open **Attendance**, choose the meal date or session, and load the beneficiary list. Mark each person's attendance status, review the entries, and save. Use the attendance list, daily summary, or report screens to review saved records. Check the date and session before saving to avoid duplicate or incorrect entries.

## Food stock

Open **Food Stock** to review inventory. Use the add or edit actions available to your role to record an item's name, quantity, unit, and relevant dates. Update quantities when stock is received or distributed. Check low-stock and expiry views when planning meals.

## Donations

Open **Donations** to review recorded donations or enter a new one if your role allows it. Provide the donor and donation details requested by the form, then save and review the resulting record or receipt where available. The sign-in screen's guest donation option is available for one-time donations. Do not enter payment card details.

## Volunteers and schedules

Open **Volunteers** to search, view, or register volunteer records where your role permits. Use **Schedules** or availability screens, if available to your account, to review or update assigned shifts and availability. Confirm dates and times before saving changes.

## Reports

Open **Reports** and choose the report relevant to your work. Set any requested filters or date range, then generate the report. Available report types and actions depend on the website or app version and your role. Use the displayed **Print** or **Export CSV** action when it is available. Treat downloaded reports as confidential because they may contain personal or operational data.

## Users and profile

Administrators can open **Users** to review accounts and, where permitted, create users, update roles/status, or deactivate access. Do not change another user's access unless authorized.

Open **My Profile** to review your account information. If profile editing or password change is available, follow the form instructions and save the change. Contact the administrator if you need an account activation, role, or password reset. Never share your password.

## Troubleshooting and privacy

| Problem | What to try |
|---|---|
| Sign-in is rejected | Check the username and password; ask the administrator to confirm the account is active. |
| A menu item or action is missing | Your role may not have access. Ask the administrator to check your permissions. |
| Android cannot load or save data | Check the device connection and confirm the API server is running and reachable at the address configured for the app. |
| A save fails or shows an error | Check required fields and input formats, retry once, and contact the administrator if the error continues. |
| A record appears incorrect | Reopen and correct it if permitted; otherwise report the module, steps, and approximate time to the administrator. |

When reporting an issue, do not include passwords, access tokens, or unnecessary beneficiary personal information. Share only the details needed to reproduce the problem.
