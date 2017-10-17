# Changelog
All notable changes to this project will be documented in this file.

## Unreleased - Delphi

### Added
- Preferences to allow customization for admins and users as well
- Global search, Supports
  - **Contexts** by...
    - name
    - lasteditor
    - updated_at (supported format is `MM.DD.YYYY <DayName> <MonthName>`)
    - Context-Type
    - Attributes (Values and Labels)
  - **Files** by...
    - Tags
    - Copyright
    - Description
    - lasteditor
  - **Layers** by...
    - Name
    - URL
  - **Sources** _(click on the star icon in the results to jump to the source entry)_ by...
    - description
    - lasteditor (literature, source and context)
  - **Literature** by...
    - all fields
  - **Users** by...
    - Name
    - E-Mail
### Changed
### Fixed
### Removed/Deprecated

## 0.3.5
### Added
- Display csv as table (with support for different delimiters)
- Display context type's layertype in Data Model Editor
- citation key in bibliography table (update/overwrite now based on citation key)
- Vive support for 3D viewer
- Display reference-indicator if certainty is changed

## 0.3.4
### Fixed
- Fix missing values of metadata in file popup
- Marker popup form styles
- Opacity of layers (Storing was not possible, stored value was ignored)
### Added
- List linked contexts in file popup
- Exif Data viewer for image files (JPEG and TIFF)
- Display coordinates in DMS (Degree Minute Second) in marker popup

## 0.3.3
### Fixed
- use current selected language for search requests
- encode search term in context search (fix missing results for terms with special chars, e.g. umlauts)

## 0.3.2
### Fixed
- The fab-styled save button (context detail view upper right corner) didn't work
- The _Last modified_ info was not updated after saving the context
- The _Toggle Edit Mode_ button is now disabled on not-supported states (should only be visible in the main state with the tree, detail view and map/files)

## 0.3.1

### Fixed
- Context Search (didn't work at all)
- Possible Error retrieving files without a thumbnail (every file except images)

## 0.3 - Cannae

### Added
- Drag & Drop Support for Element Tree
- More Datatypes (Integer, Geography, Slider, Boolean)
- Dynamic UI (The three columns on the main view can be shrinked/extended, enable Edit Mode in the Settings Dropdown menu)
- Basic Unit-Testing (Lumen API)
- State-based Routing using [ui-router](https://github.com/angular-ui/ui-router)
  - Shareable Links
  - Redirect to last state after (re)login
- Unset Selected Values (`string-sc` aka Single Dropdown)
### Changed
- Photo Viewer => File Viewer (Please read section _Protected Files_ in [INSTALL.md](INSTALL.md) for proper setup)
  - PDFs ([pdf.js](https://github.com/mozilla/pdf.js) & [angularjs-pdf](https://github.com/sayanee/angularjs-pdf))
  - Text documents (including text highlighting using [highlight.js](https://highlightjs.org/))
  - 3D files (`.dae` and `.obj`/`.mtl`, using [three.js](https://github.com/mrdoob/three.js/))
  - Markdown files ([marked](https://github.com/chjj/marked) & [angular-marked](https://github.com/Hypercubed/angular-marked))
  - Audio/Video files
- Remove input field placeholders
- Layer Control
  - Add layers for all existing contexttypes + one layer for unlinked geodata objects
  - Contexttype layers are restricted to a type (either MultiPoint, MultiLinestring or Multipolygon)
  - Geodata snapping
  - Add Bing and Google Maps as Tile-Provider
  - Layer Editor
    - Change color
    - Enable/Disable layers (**only** overlays, on startup)
    - Set opacity
    - Set default baselayer
  - Real RESTful API
### Fixed
- Display context-type in properties tab again
- Store epochs (without exact date span)
- Add/Remove items from a list
- Display thumbnail after image upload
- Show tags (enabling _Show tags_ in filter tab didn't change anything)
- Search bar in tree column (Search results couldn't be expanded, thus children were not accessible)
- Deleting Context-Types
- Store/Retrieve double (datatype) attribute values
- Missing Attributes in Lumen Seeders
- Missing translation (Context datatype label)
- Missing dimension units in dropdown
### Removed/Deprecated

## 0.2 - Babylon

### Added
- Data Model Editor (Add/Delete Attributes, Add/Delete ContextTypes)
- Tree Search Bar
- Bibtex Importer
- Add Info Popup (left bottom corner)
- Guest Account and default Admin Account
### Changed
- New Design (Based on Material Design)
- Some other Style improvements (Geodata, Login Page, ...)
- Extended Photo Functionality
  - Delete Photos
  - Edit Photo Properties
  - Add Tags
  - Search By Tag, Camera Model and Date
  - Update to Leaflet 1.x
### Fixed
### Removed/Deprecated

## 0.1 - Atlantis

### Added
- Objects Tree View
- Object Property Editor
- Geomap
- Data Analysis (based on https://github.com/eScienceCenter/dbWebGen)
- Bibliography Manager
- Photo Manager: upload and linking
- User Manager: users, roles, permissions
