'use strict'

import Vue from 'vue'
import VueI18n from 'vue-i18n'
import colorTheme from '@themes/mobicoop.js'
import '@mdi/font/css/materialdesignicons.css'

import Vuetify, {
  // general
  VApp, VContainer, VFlex, VLayout, VContent, VSpacer, VAlert, VRow, VCol,
  // header
  VToolbar, VToolbarTitle, VBtn, VImg,
  // footer
  VFooter, VChip, VCardText,
  // content
  VForm, VTextField, VCard, VCardActions
} from 'vuetify/lib'

Vue.use(Vuetify, {
  components: {
    // general
    VApp, VContainer, VFlex, VLayout, VContent, VSpacer, VAlert, VRow, VCol,
    // header
    VToolbar, VToolbarTitle, VBtn, VImg,
    // footer
    VFooter, VChip, VCardText,
    // content
    VForm, VTextField, VCard, VCardActions
  }
})
Vue.use(VueI18n)

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
