<template>
  <v-dialog
    v-model="dialog"
    max-width="800"
    @click:outside="closeDialog()"
  >
    <v-card>
      <v-toolbar
        color="primary"
      >
        <v-toolbar-title class="toolbar">
          {{ $t('loginOrRegisterTitle') }}
        </v-toolbar-title>
        
        <v-spacer />

        <v-btn 
          icon
          @click="closeDialog()"
        >
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </v-toolbar>

      <v-card-text>
        <p class="text--primary ma-1">
          {{ $t('loginOrRegister') }}
        </p>
      </v-card-text>

      <v-card-actions v-if="showLoginBtn || showRegisterBtn">
        <v-spacer />
        <v-btn
          v-if="showLoginBtn"
          rounded
          color="secondary"
          large
          :href="hrefLogin"
        >
          <span>
            {{ $t('login') }}
          </span>
        </v-btn>
        <v-btn
          v-if="showRegisterBtn"
          rounded
          color="secondary"
          large
          :href="hrefRegister"
        >
          <span>
            {{ $t('register') }}
          </span>
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>
<script>
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/utilities/LoginOrRegisterFirst/";
export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    },
  },  
  props:{
    id:{
      type: Number,
      default: null
    },
    type:{
      type: String,
      default: 'default'
    },
    showDialog:{
      type: Boolean,
      default: false
    },
    showRegisterBtn:{
      type: Boolean,
      default: true
    },
    showLoginBtn:{
      type: Boolean,
      default: true
    }
  },
  data() {
    return {
      dialog: this.showDialog
    }
  },
  computed: {
    hrefLogin() {
      if (this.id === null && this.type !== 'publish') return this.$t("loginUrl");
      switch (this.type) {
      case 'proposal':
        return this.$t("loginUrlProposalId", {"id":this.id} );
      case 'event':
        return this.$t("loginUrlEventId", {"id":this.id} );
      case 'publish':
        return this.$t("loginUrlPublish");
      default:
        return this.$t("loginUrl");
      }
    },
    hrefRegister() {
      if (this.id === null && this.type !== 'publish') return this.$t("registerUrl");
      switch (this.type) {
      case 'proposal':
        return this.$t("registerUrlProposalId", {"id":this.id} );
      case 'event':
        return this.$t("registerUrlEventId", {"id":this.id} );
      case 'publish':
        return this.$t("registerUrlPublish");
      default:
        return this.$t("registerUrl");
      }
    }
  },
  watch:{
    showDialog(){
      this.dialog = this.showDialog;
    },
  },
  methods:{
    closeDialog(){
      this.$emit('closeLoginOrRegisterDialog');
    }
  }
}
</script>