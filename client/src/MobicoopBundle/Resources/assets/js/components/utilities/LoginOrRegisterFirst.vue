<template>
  <v-dialog
    v-model="dialog"
    max-width="800"
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
          @click="dialog = false"
        >
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </v-toolbar>

      <v-card-text>
        <p class="text--primary ma-1">
          {{ $t('loginOrRegister') }}
        </p>
      </v-card-text>

      <v-card-actions>
        <v-spacer />
        <v-btn
          v-if="showLoginBtn"
          rounded
          color="secondary"
          large
          :href="proposalId ? $t('loginUrl') : $t('loginUrlProposalId',{'id':proposalId})"
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
          :href="proposalId ? $t('registerUrl') : $t('registerUrlProposalId',{'id':proposalId})"
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
import {messages_en, messages_fr} from "@translations/components/utilities/LoginOrRegisterFirst/";
export default {
  i18n: {
    messages: {
      'en': messages_en,
      'fr': messages_fr
    },
  },  props:{
    proposalId:{
      type: Number,
      default: null
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
  watch:{
    showDialog(){
      this.dialog = this.showDialog;
    }
  }
}
</script>