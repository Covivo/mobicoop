'use strict'

import Vue from 'vue'
import VueI18n from 'vue-i18n'
import colorTheme from '@themes/mobicoop.json'
import '@mdi/font/css/materialdesignicons.css'

import Vuetify, { 
  // general
  VApp, VContainer, VFlex, VLayout, VContent, VSpacer,
  // header
  VToolbar, VToolbarTitle, VBtn, VImg,
  // footer
  VFooter, VChip, VCardText,
  // content
  // messages
  VTabs, VTab, VTabItem, VTimeline, VTimelineItem, VTextarea, VIcon, VCard, VCardTitle, VDialog, VProgressLinear, VAvatar, VDivider, VCardActions, VTooltip
} from 'vuetify/lib'

Vue.use(Vuetify, {
  components: {
    // general
    VApp, VContainer, VFlex, VLayout, VContent, VSpacer,
    // header
    VToolbar, VToolbarTitle, VBtn, VImg,
    // footer
    VFooter, VChip, VCardText,
    // content
    // messages
    VTabs, VTab, VTabItem, VTimeline, VTimelineItem, VTextarea, VIcon, VCard, VCardTitle, VDialog, VProgressLinear, VAvatar, VDivider, VCardActions, VTooltip
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