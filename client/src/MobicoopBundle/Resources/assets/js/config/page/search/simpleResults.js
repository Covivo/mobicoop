'use strict'

import Vue from 'vue'
import VueI18n from 'vue-i18n'
import colorTheme from '@themes/mobicoop.json'
import '@mdi/font/css/materialdesignicons.css'

import Buefy from 'buefy'; // TODO ☣️ remove it when not needed anymore
import VueFormWizard from 'vue-form-wizard';  // TODO ☣️ remove it when not needed anymore
import 'vue-form-wizard/dist/vue-form-wizard.min.css'; // TODO ☣️ remove it when not needed anymore

import Vuetify, { 
  // general
  VApp, VContainer, VFlex, VLayout, VContent, VSpacer,
  // header
  VToolbar, VToolbarTitle, VBtn, VImg,
  // footer
  VFooter, VCardText,
  // geocomplete
  VAutocomplete, VList, VListItem, VListItemTitle, VListItemSubtitle, VListItemAvatar, VListItemContent, VIcon
} from 'vuetify/lib'

Vue.use(Vuetify, {
  components: {
    // general
    VApp, VContainer, VFlex, VLayout, VContent, VSpacer,
    // header
    VToolbar, VToolbarTitle, VBtn, VImg,
    // footer
    VFooter, VCardText,
    // geocomplete
    VAutocomplete, VList, VListItem, VListItemTitle, VListItemSubtitle, VListItemAvatar, VListItemContent, VIcon
  }
})
Vue.use(VueI18n)

Vue.use(Buefy, { // TODO ☣️ remove it when not needed anymore
  defaultTooltipType: 'is-mobicoopgreen'
});
Vue.use(VueFormWizard); // TODO ☣️ remove it when not needed anymore

const i18n = new VueI18n({
  locale: 'fr' // set locale
})

const vuetify = new Vuetify({
  icons: {
    iconfont: 'mdi', // default - only for display purposes (https://materialdesignicons.com/)
  },
  theme: colorTheme
})

export {
  Vue,
  vuetify,
  i18n
}