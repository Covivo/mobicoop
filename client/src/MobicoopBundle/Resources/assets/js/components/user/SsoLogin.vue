<template>
  <div
    v-if="buttonIcon"
    :style="'max-width:'+maxWidth+'px;'"
  >
    <v-tooltip bottom>
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
</template>
<script>
import Translations from "@translations/components/user/SsoLogin.json";

export default {
  i18n: {
    messages: Translations,
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
    service:{
      type: String,
      default: null
    },
    maxWidth:{
      type: Number,
      default:200
    }
  },
  methods:{
    click(){
      window.location.href = ((this.url) ? this.url : '/');
    }
  }
}
</script>