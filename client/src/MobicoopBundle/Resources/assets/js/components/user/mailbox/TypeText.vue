<template>
  <v-main>
    <v-container>
      <v-row>
        <v-col class="col-9">
          <v-textarea
            v-model="textToSend"
            :disabled="recipientBlocked"
            outlined
            name="typedMessage"
            :label="!recipientBlocked ? $t('enterMessage') : $t('blocked')"
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
import Translations from "@translations/components/user/mailbox/TypeText.json";

export default {
  i18n: {
    messages: Translations,
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
    recipientBlocked: {
      type: Boolean,
      default: false
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
