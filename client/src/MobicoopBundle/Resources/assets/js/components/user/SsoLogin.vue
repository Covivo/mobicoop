<template>
  <div
    v-if="buttonIcon"
    :style="'max-width:'+maxWidth+'px;'"
  >
    <v-img
      id="buttonWithImage"
      src="/images/sso/GLConnect-sso-login.png"
      style="cursor:pointer"
      @click="click"
    />
  </div>
  <v-btn
    v-else
    @click="click"
  >
    {{ service }}
  </v-btn>
</template>
<script>
export default {
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
  computed:{
    urlButtonIcon(){
      return '/'.this.buttonIcon;
    }
  },
  methods:{
    click(){
      window.location.href = ((this.url) ? this.url : '/');
    }
  }
}
</script>