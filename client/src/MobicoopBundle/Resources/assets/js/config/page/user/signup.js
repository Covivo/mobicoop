'use strict'

import Vue from 'vue'
import VueI18n from 'vue-i18n'
import colorTheme from '@themes/mobicoop.json'
import '@mdi/font/css/materialdesignicons.css'

import Vuetify, {
  // general
  VApp, VContainer, VFlex, VLayout, VCol, VRow, VContent, VSpacer, VAlert, VSwitch,//TODO : delete vswitch
  // header
  VToolbar, VToolbarTitle, VBtn, VImg,
  // footer
  VFooter, VChip, VCardText,
  // content
  VForm, VTextField, VCheckbox, VSelect, VSubheader,
  // geocomplete
  VAutocomplete, VList, VListItem, VListItemTitle, VListItemSubtitle, VListItemAvatar, VListItemContent, VIcon
} from 'vuetify/lib'

Vue.use(Vuetify, {
  components: {
    // general
    VApp, VContainer, VFlex, VLayout, VCol, VRow, VContent, VSpacer, VAlert, VSwitch,
    // header
    VToolbar, VToolbarTitle, VBtn, VImg,
    // footer
    VFooter, VChip, VCardText,
    // content
    VForm, VTextField, VCheckbox, VSelect, VSubheader,
    // geocomplete
    VAutocomplete, VList, VListItem, VListItemTitle, VListItemSubtitle, VListItemAvatar, VListItemContent, VIcon
  }
})
Vue.use(VueI18n)

// Vue.use(Buefy, { // TODO ☣️ remove it when not needed anymore
// });
// Vue.use(VueFormWizard); // TODO ☣️ remove it when not needed anymore

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