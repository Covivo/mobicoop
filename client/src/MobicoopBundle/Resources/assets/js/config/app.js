'use strict'

import Vue from 'vue'
import VueI18n from 'vue-i18n'
import colorTheme from '@themes/theme.js'
import '@mdi/font/scss/materialdesignicons.scss'

import Vuetify, {
  // general
  VApp, VContainer, VFlex, VLayout, VContent, VSpacer, VRow, VCol, VForm, VChip, VAlert, VTextarea, VTextField, VSelect, VAutocomplete, VTooltip, VSwitch, VMenu, VDatePicker, VIcon,
  // header
  VToolbar, VToolbarTitle, VBtn, VImg,
  // footer
  VFooter, VCardText
} from 'vuetify/lib'

Vue.use(Vuetify, {
  components: {
    // general
    VApp, VContainer, VFlex, VLayout, VContent, VSpacer, VRow, VCol, VForm, VChip, VAlert, VTextarea, VTextField, VSelect, VAutocomplete, VTooltip, VSwitch, VMenu, VDatePicker, VIcon,
    // header
    VToolbar, VToolbarTitle, VBtn, VImg,
    // footer
    VFooter, VCardText
  }
})
Vue.use(Vuetify)
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
