'use strict'

import Vue from 'vue'
import VueI18n from 'vue-i18n'
import colorTheme from '@themes/mobicoop.json'
import '@mdi/font/css/materialdesignicons.css'
import {LMap, LTileLayer, LMarker, LTooltip, LIcon, LPolyline} from 'vue2-leaflet' 

import { Icon }  from 'leaflet'
import 'leaflet/dist/leaflet.css'

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
  VApp, VContainer, VFlex, VLayout, VContent, VSpacer,
  // header
  VToolbar, VToolbarTitle, VBtn, VImg,
  // footer
  VFooter, VCardText, VDataTable, VChip, VTextField, VRow, VCol, VCard, VIcon,VCardTitle, VDataIterator,VTab, VTabs,VTabItem, VTabsItems,
  VTabsSlider,VDivider, VList, VListItem, VListItemContent,VForm,VAutocomplete, VMenu, VDatePicker, VTooltip, VSwitch,VListItemAvatar,
  VListItemTitle,VListItemSubtitle,VData,VDataFooter,VDataTableHeader,VDialog, VCardActions, VTextarea, VBadge, VItemGroup, VListItemGroup, VListItemIcon, VSelect, VAvatar, VSnackbar, VProgressCircular,
  // create form
  VFileInput,
} from 'vuetify/lib'

Vue.use(Vuetify, {
  components: {
    // general
    VApp, VContainer, VFlex, VLayout, VContent, VSpacer,
    // header
    VToolbar, VToolbarTitle, VBtn, VImg,
    // footer
    VFooter, VCardText, VDataTable, VChip, VTextField, VRow, VCol, VCard, VIcon, VCardTitle, VDataIterator, VTab, VTabs,VTabItem, VTabsItems,
    VTabsSlider,VDivider, VList, VListItem, VListItemContent,VForm, VAutocomplete, VMenu, VDatePicker, VTooltip, VSwitch, VListItemAvatar,
    VListItemTitle,VListItemSubtitle,VData,VDataFooter,VDataTableHeader,VDialog, VCardActions, VTextarea, VBadge, VItemGroup, VListItemGroup, VListItemIcon, VSelect, VAvatar,VSnackbar, VProgressCircular,
    //Vue2Leaflet
    LMap, LTileLayer, LMarker, LTooltip, LIcon, LPolyline,
    // create form
    VFileInput,
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
  dataFooter: {
    'items-per-page-text': 'Nombre de tuples par page'
  },
  theme: colorTheme
})

export {
  Vue,
  vuetify,
  i18n
}