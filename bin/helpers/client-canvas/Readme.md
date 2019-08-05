$$NAME$$
=======

![logo](logo.png)

*DO NOT FORGET TO CHANGE THE APP_NAME INSIDE .env after creating client*
*FIND & REPLACE $$URL$$ & CHANGE IT TO THE URL OF THE DOMAINE WITHOUT dev/test/prod BUT LAB IF NEEDED*


$$NAME$$ is based on [mobicoop-plateform](https://gitlab.com/mobicoop/mobicoop-platform)

_for developpement you have to use the same branch of mobicoop for eg (dev mobicoop with dev $$NAME$$, master mobicoop=> master $$NAME$$)_


# 🐳 With Docker 🐳 


## 🐳 Requirements


Please check mobicoop [docker requirement](https://gitlab.com/mobicoop/mobicoop-platform/tree/dev/docs#-requirements-) 

Clone & install via [docker way](https://gitlab.com/mobicoop/mobicoop-platform/tree/dev/docs#-install) the mobicoop platform

⚡️ Export env variable inside your .zshrc/.bashrc : `export MOBICOOP_CLIENT=/Full/Path/TO/mobicoopl-platform/client`  *(client folder not the bundle!)*

## 🐳 Install 

`make install`


## 🐳 Start 

1. Start Mobicoop
2. Execute mobicoop fixtures
3. `make start`

you can now access $$NAME$$ from `localhost:9091`


## Infos


☣️ If you have to exclude files on mobicoop while developping, *DO NOT FORGET TO EXCLUDE THEM INTO THE [gitlab-exclude](./gitlab-exclude) FILE OF $$NAME$$* ☣️

*If you change any file into the $$NAME$$ mobicoop bundle, files are automatically changed in the mobicoop-platform folder too,  so you will have to commit from the mobicoop-platform folder those changes*

#### Overwrite assets

If you want to overwrite a bundle assets in you client platform just recreate it inside `assets/` load the bundle one if you need the js & load you css

```javascript
import '@js/page/search/simpleResults.js';
import '@clientCss/page/search/simpleResults.scss';
```