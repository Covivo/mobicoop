'use strict'

import Vue from 'vue'
import VueI18n from 'vue-i18n'
import colorTheme from '@themes/mobicoop.json'
import '@mdi/font/css/materialdesignicons.css'
import {LMap, LTileLayer, LMarker, LIcon} from 'vue2-leaflet' 

import Vuetify, { 
  // general
  VApp, VContainer, VFlex, VLayout, VContent, VRow, VCol, VSpacer,
  // header
  VToolbar, VToolbarTitle, VBtn, VImg,
  // footer
  VFooter, VChip, VCardText,
  // geocomplete
  VAutocomplete, VList, VListItem, VListItemTitle, VListItemSubtitle, VListItemAvatar, VListItemContent, VIcon, VForm,
  // ad publish
  VStepper, VStepperHeader, VStepperStep, VDivider, VStepperItems, VStepperContent, VCheckbox, VSelect, VTextarea, VCard, VCardTitle, VRadioGroup, VRadio, VTimeline, VTimelineItem,
  // content
  VSwitch, VMenu, VDatePicker, VTimePicker, VTextField,  VTooltip
} from 'vuetify/lib'

Vue.use(Vuetify, {
  components: {
    // general
    VApp, VContainer, VFlex, VLayout, VContent, VRow, VCol, VSpacer,
    // header
    VToolbar, VToolbarTitle, VBtn, VImg,
    // footer
    VFooter, VChip, VCardText,
    // geocomplete
    VAutocomplete, VList, VListItem, VListItemTitle, VListItemSubtitle, VListItemAvatar, VListItemContent, VIcon, VForm,
    // ad publish
    VStepper, VStepperHeader, VStepperStep, VDivider, VStepperItems, VStepperContent, VCheckbox, VSelect, VTextarea, VCard, VCardTitle, VRadioGroup, VRadio, VTimeline, VTimelineItem,
    // content
    VSwitch, VMenu, VDatePicker, VTimePicker, VTextField,  VTooltip,
    //Vue2Leaflet
    LMap, LTileLayer, LMarker, LIcon
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