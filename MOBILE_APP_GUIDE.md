# Tharimpepe FSMS Android App Guide

This project now supports two access paths:

- Website: users open the hosted PHP system in a browser.
- Android app: users install the Play Store app, which opens the same hosted FSMS system in a native Android shell.

## Important Architecture

The Android app does not run PHP locally on the phone. The PHP system must be hosted online with HTTPS, and the Android app loads that hosted URL.

Use this model:

```text
Android app -> https://your-fsms-domain.com -> PHP FSMS + database
Website     -> https://your-fsms-domain.com -> PHP FSMS + database
```

## Configure The Live Website URL

Edit `capacitor.config.json` and replace:

```json
"url": "https://replace-with-your-fsms-domain.example"
```

with your real production URL, for example:

```json
"url": "https://fsms.yourdomain.com"
```

Keep `cleartext` set to `false` for Play Store builds. The app should use HTTPS.

## Sync Android After Config Changes

Run:

```powershell
npm run mobile:sync
```

## Open In Android Studio

Run:

```powershell
npm run mobile:open
```

Android Studio will open the generated `android/` project.

## Run On Your Phone

1. Enable Developer Options on your Android phone.
2. Enable USB debugging.
3. Connect the phone to the PC with USB.
4. Open the project in Android Studio.
5. Select your phone as the device.
6. Press Run.

## Build For Testing

Use Android Studio:

```text
Build > Build Bundle(s) / APK(s) > Build APK(s)
```

This creates an APK for local testing.

## Build For Play Store

Use Android Studio:

```text
Build > Generate Signed Bundle / APK > Android App Bundle
```

Google Play prefers an `.aab` file for store publishing.

Before publishing, prepare:

- Production HTTPS website URL
- App icon and splash assets
- App name: Tharimpepe FSMS
- Package name: `com.tharimpepe.fsms`
- Version name and version code
- Privacy policy URL
- Test login account for Google Play review if the app requires authentication

## Local Java Note

The current machine has `JAVA_HOME` set incorrectly. It likely points to the `jbr` folder but needs to point to the root of the JDK where the `bin` folder resides.

**To Fix:**
1. Locate your JDK installation (e.g., `C:\Program Files\Android\Android Studio\jbr`).
2. Set your System Environment Variable `JAVA_HOME` to that path.
3. Ensure `%JAVA_HOME%\bin` is in your `Path` variable.

After that, this should work:

```powershell
.\android\gradlew.bat -p android assembleDebug
```
