# Theme Check Extended

The initial version of this plugin developed by [Otto42 press](http://ottopress.com/).

The current version of the plugin gives the possibility to theme developers to exclude some of the theme folders and files.

In modern theme development workflow, we usually include several other files in the theme folder related to the theme development process. 

Usually, in theme development folder we include folders and files like `node_modules`, `resources`, `test`, `.gitignore`, `gulpfile.js`, `Gruntfile.js`, etc.

That in turn has as a result, the `Theme Check` plugin to investigate those _files and folders_, and the final report is not realistic enough.

By using this version of the plugin, you are free to exclude the files and folders that are not part of your final project and thus get more realistic output in the generated report.

## Usage

In the `exclude` textarea field, add one path or a file per line and then run the reports as you know. Note the paths should be relative to your theme root folder. 

In example:

```
assets/css/scss
assets/css/sprites
assets/img/icons
assets/img/raw
assets/js/custom
.gitignore
package.json
package-lock.json
```
