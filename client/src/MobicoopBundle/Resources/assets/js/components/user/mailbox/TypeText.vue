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

import { merge } from "lodash";
import {messages_en, messages_fr} from "@translations/components/user/mailbox/TypeText/";
import {messages_client_en, messages_client_fr} from "@clientTranslations/components/user/mailbox/TypeText/";

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);

export default {
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'fr': MessagesMergedFr
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
