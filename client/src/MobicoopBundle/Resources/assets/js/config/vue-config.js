'use strict';

import Vue from 'vue'
import Vuetify, { VList, VListItem, VListItemAvatar, VListItemContent, VListItemTitle, VListItemSubtitle, VCardText, VSwitch, VIcon, VTextField, VApp, VLayout, VContainer, VFlex, VBtn, VToolbar, VToolbarTitle, VImg, VSpacer, VContent, VAutocomplete, VTooltip, VMenu, VDatePicker, VFooter } from 'vuetify/lib'
import VueI18n from 'vue-i18n'
import colorTheme from '@themes/mobicoop.json';
import '@mdi/font/css/materialdesignicons.css' // Ensure you are using css-loader

Vue.use(Vuetify, {
  components: {
    VList, VListItem, VListItemAvatar, VListItemContent, VListItemTitle, VListItemSubtitle, 
    VCardText, VSwitch, VIcon, VTextField, VApp, VLayout, VContainer, VFlex, VBtn, VToolbar, VToolbarTitle, VImg, VSpacer, VContent, VAutocomplete, VTooltip, VMenu, VDatePicker, VFooter
  }
});
Vue.use(VueI18n);

const i18n = new VueI18n({
  locale: 'fr' // set locale
});

const vuetify = new Vuetify({
  icons: {
    iconfont: 'mdi', // default - only for display purposes (https://materialdesignicons.com/)
  },
  theme: colorTheme
});

export {
  Vue,
  vuetify,
  i18n
}