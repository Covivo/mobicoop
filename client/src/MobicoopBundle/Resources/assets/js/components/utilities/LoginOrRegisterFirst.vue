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

      <v-card-actions v-if="proposalId">
        <v-spacer />
        <v-btn
          v-if="showLoginBtn"
          rounded
          color="secondary"
          large
          :href="proposalId ? $t('loginUrlProposalId',{'id':proposalId}) : $t('loginUrl')"
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
          :href="proposalId ? $t('registerUrlProposalId',{'id':proposalId}) : $t('registerUrl')"
        >
          <span>
            {{ $t('register') }}
          </span>
        </v-btn>
      </v-card-actions>
      <v-card-actions v-if="eventId">
        <v-spacer />
        <v-btn
          v-if="showLoginBtn"
          rounded
          color="secondary"
          large
          :href="eventId ? $t('loginUrlEventId',{'id':eventId}) : $t('loginUrl')"
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
          :href="eventId ? $t('registerUrlEventId',{'id':eventId}) : $t('registerUrl')"
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
    proposalId:{
      type: Number,
      default: null
    },
    eventId:{
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
    },
    initDestination: {
      type: Object,
      default: null
    },
    event: {
      type: Object,
      default: null
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
    },
  },
  methods:{
    closeDialog(){
      this.$emit('closeLoginOrRegisterDialog');
    }
  }
}
</script>