## Submitting issues
If you have questions about how to install or use Spacialist, please send us an [email](mailto:spacialist@dh-center.uni-tuebingen.de).

### Guidelines
- Please search the existing issues first, it's likely that your issue was already reported or even fixed.
  1. Click _Issues_ and type any word in the top search/command bar.
  2. You can also filter by appending e. g. `label:to-do` to the search string.
  3. More info on [search syntax within github](https://help.github.com/articles/searching-issues)

Help us to maximize the effort we can spend fixing issues and adding new features, by not reporting duplicate issues.

Use your real name (sorry, no pseudonyms or anonymous contributions).
If you set your `user.name` and `user.email` git configs, you can sign your
commit automatically with `git commit -s`. You can also use git [aliases](https://git-scm.com/book/tr/v2/Git-Basics-Git-Aliases)
like `git config --global alias.ci 'commit -s'`. Now you can commit with
`git ci` and the commit will be signed.

## Translations
Translations are currently in the [i18n](https://github.com/DH-Center-Tuebingen/Spacialist/tree/master/resources/js/i18n) (Frontend) and [lang](https://github.com/DH-Center-Tuebingen/Spacialist/tree/master/resources/lang) (Backend) subfolder. See existing languages for required files and structure. If you want to add a new language please note that your language has to be added to [app.js](https://github.com/DH-Center-Tuebingen/Spacialist/tree/master/resources/app.js) file.
