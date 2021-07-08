<template>
  <v-main>
    <v-container>
      <v-row>
        <v-col class="col-9">
          <v-textarea
            v-model="textToSend"
            :disabled="recipientBlockedId!==null"
            outlined
            name="typedMessage"
            :label="recipientBlockedId==null ? $t('enterMessage') : $t('blocked')"
            background-color="#FFFFFF"
            rows="2"
          />
        </v-col>
        <v-col
          class="col-3"
          align-self-center
        >
          <div class="text-xs-center">
            <v-btn
              id="validSendMessage"
              class="mx-2 black--text font-weight-bold"
              fab
              rounded
              color="secondary"
              :disabled="textToSend===''"
              :loading="loading"
              @click="emit()"
            >
              <v-icon color="white">
                mdi-send
              </v-icon>
            </v-btn>
          </div>
        </v-col>
      </v-row>
    </v-container>
  </v-main>
</template>
<script>

import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/mailbox/TypeText/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  props: {
    idThreadMessage: {
      type: Number,
      default: null
    },
    idRecipient: {
      type: Number,
      default: null
    },
    loading: {
      type: Boolean,
      default: null
    },
    recipientBlockedId: {
      type: Number,
      default: null
    }
  },
  data(){
    return{
      textToSend:"",
    }
  },
  methods:{
    emit(message){
      this.$emit("sendInternalMessage",
        {
          idThreadMessage:this.idThreadMessage,
          idRecipient:this.idRecipient,
          textToSend:this.textToSend
        });
      this.textToSend = "";
    }
  }
}
</script>
