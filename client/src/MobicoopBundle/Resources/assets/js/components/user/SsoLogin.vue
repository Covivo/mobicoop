<template>
  <div>
    <div
      v-if="useButtonIcon && buttonIcon"
      :style="'max-width:'+maxWidth+'px;'"
      class="mx-auto"
    >
      <v-tooltip
        bottom
      >
        <template v-slot:activator="{ on, attrs }">
          <v-img
            id="buttonWithImage"
            :src="buttonIcon"
            style="cursor:pointer"
            v-bind="attrs"
            v-on="on"
            @click="click"
          />
        </template>
        <span>{{ $t('useSsoService', {'service':service}) }}</span>
      </v-tooltip>
    </div>
    <v-btn
      v-else
      class="pa-5"
      color="secondary"
      type="button"
      rounded
      :disabled="!buttonActive"
      @click="click"
    >
      {{ $t('useSsoService', {'service':service}) }} <span v-if="picto"><img
        :src="picto"
        width="30"
      ></span>
    </v-btn>
  </div>
</template>
<script>

import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/SsoLogin/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  props:{
    url:{
      type: String,
      default: null
    },
    buttonIcon:{
      type: String,
      default: null
    },
    picto:{
      type: String,
      default: null
    },
    useButtonIcon:{
      type: Boolean,
      default: null
    },
    service:{
      type: String,
      default: null
    },
    ssoProvider:{
      type: String,
      default: null
    },
    maxWidth:{
      type: Number,
      default:200
    },
    defaultButtonsActive: {
      type: Boolean,
      default: true
    }
  },
  data(){
    return {
      buttonActive: this.defaultButtonsActive
    }
  },
  computed:{
    ssoButtonsActiveStatus(){
      return this.$store.getters['sso/ssoButtonsActiveStatus'];
    },
    refreshActiveButtons(){
      return this.$store.getters['sso/refreshActiveButtons'];
    }
  },
  watch:{
    refreshActiveButtons(){
      this.buttonActive = this.ssoButtonsActiveStatus[this.ssoProvider];
      this.$store.commit('sso/setRefreshActiveButtons', false);
    }
  },
  methods:{
    click(){
      window.location.href = ((this.url) ? this.url : '/');
    }
  }
}
</script>
