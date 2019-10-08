<template>
  <v-content>
    <v-container>
      <v-row>
        <v-col class="col-9">
          <v-textarea
            v-model="textToSend"
            name="typedMessage"
            filled
            :label="$t('ui.form.enterMessage')"
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
//import axios from "axios";
export default {
  props: {
  },
  data(){
    return{
    }
  },
  mounted(){
  },
  methods:{
    sendInternalMessage(){
    }
  }
}
</script>