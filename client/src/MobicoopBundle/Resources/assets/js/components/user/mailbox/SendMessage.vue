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
              @click="sendInternalMessage()"
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
import axios from "axios";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/user/mailbox/SendMessage.json";

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
    idAskHistory: {
      type: Number,
      default: null
    }
  },
  data(){
    return{
      textToSend:""
    }
  },
  mounted(){
  },
  methods:{
    sendInternalMessage(){
      let messageToSend = {
        idThreadMessage: this.idThreadMessage,
        text: this.textToSend,
        idRecipient: this.idRecipient,
        idAskHistory: (this.idAskHistory !== null) ? this.idAskHistory : null
      };
      axios.post("/utilisateur/messages/envoyer", messageToSend).then(res => {
        this.emit(res.data);
        this.textToSend = "";
      });
    },
    emit(message){
      this.$emit("updateThreadDetails",{idThreadMessage:this.idThreadMessage});
    }
  }
}
</script>