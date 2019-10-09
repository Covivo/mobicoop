<template>
  <v-content>
    <v-container>
      <v-row>
        <v-col class="col-9">
          <v-textarea
            v-model="textToSend"
            name="typedMessage"
            filled
            :label="$t('enterMessage')"
            auto-grow
            rows="2"
            background-color="#FFFFFF"
            value
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
              color="primary"
              :disabled="this.textToSend===''"
              @click="emit()"
            >
              <v-icon>mdi-send</v-icon>
            </v-btn>
          </div>
        </v-col>
      </v-row>
    </v-container>
  </v-content>
</template>
<script>
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/user/mailbox/TypeText.json";

export default {
  i18n: {
    messages: Translations,
    sharedMessages: CommonTranslations
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
  },
  data(){
    return{
      textToSend:""
    }
  },
  mounted(){
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