'use strict'

import Vue from 'vue'
import VueI18n from 'vue-i18n'
import colorTheme from '@themes/theme.js'
import CommonTranslations from "@translations/translations.js"
import '@mdi/font/scss/materialdesignicons.scss'
import {LMap, LTileLayer, LMarker, LTooltip, LIcon, LPolyline} from 'vue2-leaflet' 

import { Icon }  from 'leaflet'

//***  this part resolve an issue where the markers would not appear
import iconRetinaUrl from 'leaflet/dist/images/marker-icon-2x.png'
import iconUrl from 'leaflet/dist/images/marker-icon.png'
import shadowUrl from 'leaflet/dist/images/marker-shadow.png'
delete Icon.Default.prototype._getIconUrl;
Icon.Default.mergeOptions({
  iconRetinaUrl: iconRetinaUrl,
  iconUrl: iconUrl,
  shadowUrl: shadowUrl
});
//*********/


import Vuetify, {
  // general
  VApp, VContainer, VFlex, VLayout, VContent, VSpacer, VRow, VCol, VForm, VChip, VAlert, VTextarea, VTextField, VSelect, VAutocomplete, VTooltip, VSwitch, VMenu, VDatePicker, VTimePicker, VIcon,
  VTabs, VTabsItems, VTab, VTabItem, VCheckbox, VAvatar, VCard, VCardTitle, VCardActions, VDivider, VList, VListItem, VListItemContent, VListItemTitle, VListItemSubtitle,
  VSkeletonLoader, VSnackbar, VDataIterator, VOverlay, VDialog, VSlider,
  VFileInput, VProgressCircular, VDataTable, VListItemGroup, VListItemAvatar, VStepper, VStepperHeader, VStepperStep, VStepperItems, VStepperContent, VRadioGroup, VRadio, VTimeline, VTimelineItem,
  // header
  VToolbar, VToolbarTitle, VBtn, VImg,
  // footer
  VFooter, VCardText,
} from 'vuetify/lib'

Vue.use(Vuetify, {
  components: {
    // general
    VApp, VContainer, VFlex, VLayout, VContent, VSpacer, VRow, VCol, VForm, VChip, VAlert, VTextarea, VTextField, VSelect, VAutocomplete, VTooltip, VSwitch, VMenu, VDatePicker, VTimePicker, VIcon,
    VTabs, VTabsItems, VTab, VTabItem, VCheckbox, VAvatar, VCard, VCardTitle, VCardActions, VDivider, VList, VListItem, VListItemContent, VListItemTitle, VListItemSubtitle,
    VSkeletonLoader, VSnackbar, VDataIterator, VOverlay, VDialog, VSlider,
    VFileInput, VProgressCircular, VDataTable, VListItemGroup, VListItemAvatar, VStepper, VStepperHeader, VStepperStep, VStepperItems, VStepperContent, VRadioGroup, VRadio, VTimeline, VTimelineItem,
    // header
    VToolbar, VToolbarTitle, VBtn, VImg,
    // footer
    VFooter, VCardText,
    //Vue2Leaflet
    LMap, LTileLayer, LMarker, LTooltip, LIcon, LPolyline,
  }
})

Vue.use(VueI18n)

const i18n = new VueI18n({
  locale: 'fr', // set locale
  fallbackLocale: 'fr',
  messages: CommonTranslations,
  // Suppress warnings (while keeping those which warn of the total absence of translation for the given key) when
  // the local component translations doesn't not have root ones
  silentTranslationWarn: true 
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
