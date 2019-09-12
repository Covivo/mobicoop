'use strict'

import Vue from 'vue'
import VueI18n from 'vue-i18n'
import colorTheme from '@themes/mobicoop.json'
import '@mdi/font/css/materialdesignicons.css'

import Vuetify, { 
  // general
  VApp, VContainer, VFlex, VLayout, VContent, VRow, VCol, VSpacer,
  // header
  VToolbar, VToolbarTitle, VBtn, VImg,
  // footer
  VFooter, VChip, VCardText,
  // content
  VSwitch, VMenu, VDatePicker, VTextField,  VTooltip, VCard, VCardTitle, VCardActions,
  // geocomplete
  VAutocomplete, VList, VListItem, VListItemTitle, VListItemSubtitle, VListItemAvatar, VListItemContent, VIcon, VForm,
} from 'vuetify/lib'

Vue.use(Vuetify, {
  components: {
    // general
    VApp, VContainer, VFlex, VLayout, VContent, VRow, VCol, VSpacer,
    // header
    VToolbar, VToolbarTitle, VBtn, VImg,
    // footer
    VFooter, VChip, VCardText,
    // content
    VSwitch, VMenu, VDatePicker, VTextField,  VTooltip, VCard, VCardTitle, VCardActions,
    // geocomplete
    VAutocomplete, VList, VListItem, VListItemTitle, VListItemSubtitle, VListItemAvatar, VListItemContent, VIcon, VForm
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