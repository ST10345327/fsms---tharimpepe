# Tharimpepe FSMS Figma Prototype Handoff

Source prototype: [public/fsms-prototype.html](../public/fsms-prototype.html)

## Goal
Create an interactive desktop-first Figma prototype for the Tharimpepe Feeding Scheme Management System using the existing HTML mockup as the single visual source of truth.

## File Setup
- File name: `FSMS - Tharimpepe Prototype`
- Page 1: `Prototype`
- Frame size: `1440 x 1024`
- Grid: `12 columns`, `24 px margin`, `24 px gutter`
- Main app frame corner radius: `16 px`
- App shell padding outside frame: `20 px`

## Global Styles
- Font family: `Segoe UI` or closest Figma fallback `Inter`
- Primary text: `#1F2937`
- Secondary text: `#667085`
- Primary background: `#FFFFFF`
- Secondary background: `#F8FAFC`
- Tertiary background: `#F3F6F8`
- Secondary border: `#CFD8E3`
- Tertiary border: `#DDE5EC`
- Primary green: `#1D9E75`
- Dark green: `#0F6E56`
- Blue: `#378ADD`
- Amber: `#BA7517`
- Red: `#E24B4A`
- Green badge bg: `#E1F5EE`
- Red badge bg: `#FCEBEB`
- Amber badge bg: `#FAEEDA`
- Blue badge bg: `#E6F1FB`

## Reusable Components
- `Sidebar / Nav Item`
  - Default, Hover, Active
  - Height `40 px`
  - Left border `3 px` for active state
- `Metric Card`
  - Size approx `1fr` in four-column layout
  - Label `11 px`
  - Value `22 px`, bold
- `Card`
  - White background, `16 px` padding, `16 px` radius
- `Badge`
  - Variants: Green, Amber, Red, Blue
- `Button`
  - Default and Primary
- `Input`
  - Text, Date, Select
- `Beneficiary Tile`
  - Default, Present, Absent
- `Report Button`
  - Icon tile + text block

## Prototype Frames

### 1. Dashboard
- Sidebar active item: `Dashboard`
- Topbar title: `Dashboard`
- Metrics row:
  - Total beneficiaries `148`
  - Meals served today `132`
  - Stock items low `3`
  - Active volunteers `12`
- Left card: `Recent attendance`
- Right card: `Stock summary`
- Full-width card: `Today's volunteer schedule`

### 2. Beneficiary Management
- Sidebar active item: `Beneficiaries`
- Topbar title: `Beneficiary management`
- Top card: registration form
- Bottom card: beneficiary list table with search field

### 3. Attendance Tracking
- Sidebar active item: `Attendance`
- Topbar title: `Attendance tracking`
- Top card: mark attendance
- Include interactive beneficiary tiles
- Bottom card: attendance history table

### 4. Stock and Donations
- Sidebar active item: `Stock & Donations`
- Topbar title: `Stock & donations`
- Two-column row:
  - Left: add stock or donation form
  - Right: stock level summary with progress bars
- Bottom card: donation log table

### 5. Volunteer Scheduling
- Sidebar active item: `Volunteers`
- Topbar title: `Volunteer scheduling`
- Top card: volunteer registration form
- Bottom card: weekly schedule table

### 6. Reports
- Sidebar active item: `Reports`
- Topbar title: `Reports`
- Left column: report type buttons
- Right column: report parameter form

## Prototype Links
- Clicking each sidebar item should navigate to its matching frame.
- Preserve the app shell between frames for consistent navigation.
- Optional interaction:
  - On `Attendance Tracking`, create an overlay or variant swap for beneficiary tile states `Present` and `Absent`.

## Layout Notes
- Sidebar width: `220 px`
- Topbar height: about `56 px`
- Main content padding: `20 px`
- Card gap in two-column rows: `12 px`
- Metric row gap: `12 px`
- Form layout: two columns, with address fields spanning full width

## Content Notes
- Keep names and values from the HTML prototype exactly as shown.
- Use `Monday, 20 April 2026` in the top-right topbar text.
- Keep the footer user identity as:
  - `O. Phawe`
  - `Admin`

## Build Order
1. Create color styles and text styles.
2. Build the shell: sidebar, topbar, content region.
3. Build reusable components.
4. Create the six frames from the HTML prototype.
5. Add click-through prototype links on the sidebar.
6. Export screenshots if needed for document insertion.

## Implementation Reference
- HTML prototype path: [public/fsms-prototype.html](../public/fsms-prototype.html)
- Task 2 requirement analysis: [Task2a_Requirements_Analysis.docx](./Task2a_Requirements_Analysis.docx)
- Task 2 system design: [Task2b_System_Design.docx](./Task2b_System_Design.docx)
