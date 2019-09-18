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
  VFooter, VCardText, VDataTable, VChip, VTextField, VRow, VCol, VCard, VIcon,VCardTitle, VDataIterator,VTab, VTabs,VTabItem, VTabsItems,
  VTabsSlider,VDivider, VList, VListItem, VListItemContent,VForm,VAutocomplete, VMenu, VDatePicker, VTooltip, VSwitch,VListItemAvatar,
  VListItemTitle,VListItemSubtitle,VData,VDataFooter,VDataTableHeader,VDialog, VCardActions, VTextarea, VBadge, VItemGroup, VListItemGroup, VListItemIcon, VSelect,
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
    VListItemTitle,VListItemSubtitle,VData,VDataFooter,VDataTableHeader,VDialog, VCardActions, VTextarea, VBadge, VItemGroup, VListItemGroup, VListItemIcon, VSelect,
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