# Plugins

Supported since Release 0.9 (_Isfahan_).

**Please Note!**
Plugins are in beta (since Release _Isfahan_) and installation or the overall API may change!

## Plugin structure

Before actually creating a plugin, here is how the folder structure of your plugin **has** to be:

- `App`: Hosting your Laravel Models and an `info.xml` file with several metadata of your plugin
- `Controllers`: Hosting your Laravel Controllers
- `Migration`: Place your migration files here
- `js`: This is the destination folder for your Vue/JavaScript code. **Important**: Bundle **all** your JavaScript code into a single `script.js` file!
- `routes`: Place for your (API) routes to connect your JS code with the Laravel Backend (`api.php`)

## Creating a Plugin

The easiest way to setup a new plugin is to start with an empty vue-cli project.

### Init

```bash
vue create spacialist_my_plugin
```

For further information on how to install vue-cli, please head to [their page](https://cli.vuejs.org/guide/).

Your `package.json` should look something like this:

```json
{
  // ...
  "scripts": {
    "serve": "vue-cli-service serve",
    "build": "vue-cli-service build",
    "lint": "vue-cli-service lint"
  },
  "dependencies": {
    "core-js": "^3.15.2"
  },
  "devDependencies": {
    "@vue/cli-plugin-babel": "~4.5.13",
    "@vue/cli-plugin-eslint": "~4.5.13",
    "@vue/cli-service": "~4.5.13",
    "@vue/compiler-sfc": "^3.1.5",
    "babel-eslint": "^10.1.0",
    "eslint": "^7.31.0",
    "eslint-plugin-vue": "^7.14.0",
    "vue": "^3.1.5"
  },
  "eslintConfig": {
    "root": true,
    "env": {
      "node": true
    },
    "extends": [
      "plugin:vue/vue3-essential",
      "eslint:recommended"
    ],
    "parserOptions": {
      "parser": "babel-eslint"
    },
    "rules": {
      "no-unused-vars": "off",
      "vue/no-dupe-keys": "off",
      "no-undef": "off"
    }
  },
  "browserslist": [
    "> 1%",
    "last 2 versions",
    "not dead"
  ]
}
```

Edit/Create the following files for the neccessary configs:

#### babel.config.js

```js
module.exports = {
  presets: [
    '@vue/cli-plugin-babel/preset',
  ],
}
```

#### vue.config.js

```js
module.exports = {
    productionSourceMap: false,
    chainWebpack: config => {
        config.optimization.delete('splitChunks')
    },
    filenameHashing: false,
}
```

### Folders

Then, create all the needed folders:

```bash
mkdir App
mkdir Controllers
mkdir Migration
mkdir js
mkdir -p src/components
mkdir src/i18n
```

#### info.xml

This File is mandatory and stores all the information about your plugin. Author, Licence, Version, Description, ...

```xml
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<info>
    <name>Plugin</name> <!-- is the ID of your plugin -->
    <title>Awesome Plugin</title> <!-- Title is shown in Plugin Overview page -->
    <description>This is my awesome Plugin!</description> <!--  -->
    <version>3.0.1</version> <!-- Current version. A higher version triggers running new migrations -->
    <licence>agpl</licence>
    <authors>
        <author>Vinzenz Rosenkranz</author>
    </authors>
</info>

```

#### permissions.json and role-presets.json

In `permissions.json` you can define any new permission (set) needed for your plugin. Each set is an array identified by an **unique** key. This array consists of **five** objects with three keys (`name`, `display_name` and `description`) where `name` must be one of:

1. `read`
2. `write`
3. `create`
4. `delete`
5. `share`

`role-presets.json` can be used to extend existing core role sets. It consists of an array of objects. Each object has two keys (`extends` and `rule_set`). Value of `extends` is the name of the core role-preset (e.g. `administrator` or `guest`). `rule_set` is an array of all permissions from all your sets you want that role to have (e.g. `['my_plugin_read', 'my_plugin_delete']`).

### JavaScript

Spacialist exposes two global functions inside the global `SpPS` object.
`register({})`: Is used to register a new plugin. Parameters are:

```js
{
    id: 'Your ID', // id of your plugin, used to reference that plugin in different slots
    // holds your translations per language. languageKey is e.g. en, de, fr, ... (based on vue-i18n)
    // All keys are prefixed with 'plugin.', so remember to use that prefix in t() method
    i18n: {
        languageKeyA: ...,
        languageKeyB: ...,
        // ...
    }
}
```

`intoSlot({})`: A plugin can be installed into different slots. Currently supported are `tab`, `tools` or `settings`.

```js
{
    of: 'Your ID', // mandatory,
    slot: 'tab', // slot to load that part into, defaults to tab
    icon: '', // Icon that should be displayed at the spot (FontAwesome; e.g. 'fa-folder')
    label: '', // Translation key that should be displayed at the spot (e.g. 'plugin.file.title')
    component: '', // Component you want to show in the slot (only for tab), can be template string or imported component
    componentTag: '', // Provide a tag to prevent colliding components (is prefixed by 'sp-plugin-')
}
```

#### Build

Your plugin **has** to be exported as lib without bundled Vue. Vue is globally available in Spacialist and bundled Vue would break Reactivity and other stuff (e.g. Text Nodes)

`t()` for translations is also globally available. You only have to return `t` inside your `setup()` function to use it inside your templates.

### Installing the plugin

To install a plugin simply copy the contents of the created folders above (excluding `src`) and reload the plugin page in Spacialist. You should now see your plugin as "Not installed" on the page and you can install it by clicking on the "Install" button.

Spacialist will now automatically load your bundled `script.js` file.