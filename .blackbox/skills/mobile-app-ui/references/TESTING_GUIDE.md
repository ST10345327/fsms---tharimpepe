# Testing & Debugging Guide

Complete workflow for testing mobile UI on devices and debugging issues.

## Local Development Testing

1. Start PHP server:
   ```bash
   php -S localhost:8000
   ```

2. Access mobile views directly:
   - Dashboard: `http://localhost:8000/views/dashboard/dashboard-mobile.php`
   - Check user is logged in before testing

## Chrome DevTools Mobile Testing

1. Open DevTools: `F12` or `Ctrl+Shift+I`
2. Toggle Device Toolbar: `Ctrl+Shift+M`
3. Test at these widths:
   - 375px (iPhone SE) - smallest common
   - 390px (iPhone 12) - standard mobile
   - 768px (tablet) - responsive breakpoint

4. Verify touch targets:
   - Minimum 44x44 pixels
   - Check clickable areas in Elements panel

## Remote Device Testing

1. Enable USB debugging on Android:
   - Settings → Developer Options → USB Debugging

2. Connect via Chrome:
   - Open `chrome://inspect`
   - Select device and inspect page

3. Test on iOS via Safari (Mac only):
   - Enable Web Inspector in iOS Settings
   - Develop menu in Safari → Select device

## Common Issues & Fixes

### Blank Screen
- Check PHP errors in server logs
- Verify all include paths are correct
- Ensure user session is active

### Drawer Not Opening
- Verify `mobile-nav.js` is loaded
- Check browser console for JS errors
- Ensure `data-mobile-drawer-open` attribute exists on button

### Styles Not Applied
- Verify CSS paths in `mobile-layout-start.php`
- Check network tab for 404s on CSS files
- Clear browser cache

### Touch Targets Too Small
- Ensure buttons have `min-height: 44px`
- Add padding to small elements
- Use browser devtools to measure clickable area

## Performance Checklist

- [ ] Viewport meta tag includes `viewport-fit=cover`
- [ ] Images have width/height attributes
- [ ] CSS/JS files are minified (production)
- [ ] No console errors in mobile Chrome
- [ ] Touch targets >= 44px
- [ ] Safe area insets applied for notched devices
- [ ] Dark mode colors defined