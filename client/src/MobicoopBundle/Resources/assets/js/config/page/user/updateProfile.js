/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
 * This project is dual licensed under AGPL and proprietary licence.
 ***************************
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <gnu.org/licenses>.
 ***************************
 *    Licence MOBICOOP described in the file
 *    LICENSE
 **************************/

'use strict'

import Vue from 'vue'
import VueI18n from 'vue-i18n'
import colorTheme from '@themes/mobicoop.js'
import '@mdi/font/css/materialdesignicons.css'

import Vuetify, {
  // general
  VApp, VContainer, VFlex, VCol, VRow, VLayout, VContent, VSpacer,
  // header
  VToolbar, VToolbarTitle, VBtn, VImg,
  // footer
  VFooter, VChip, VCardText,
  // content
  VForm, VTabs, VTab, VTabItem, VSnackbar, VIcon, VTextField,
  // geocomplete
  VAutocomplete, VList, VListItem, VListItemTitle, VListItemSubtitle, VListItemAvatar, VListItemContent
} from 'vuetify/lib'

Vue.use(Vuetify, {
  components: {
    // general
    VApp, VContainer, VFlex, VCol, VRow, VLayout, VContent, VSpacer,
    // header
    VToolbar, VToolbarTitle, VBtn, VImg,
    // footer
    VFooter, VChip, VCardText,
    // content
    VForm, VTabs, VTab, VTabItem, VSnackbar, VIcon, VTextField,
    // geocomplete
    VAutocomplete, VList, VListItem, VListItemTitle, VListItemSubtitle, VListItemAvatar, VListItemContent
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
