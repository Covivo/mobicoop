<template>
  <v-main>
    <thread-carpool
      v-for="(message, index) in messages"
      :key="index"
      :avatar="message.avatarsRecipient"
      :given-name="message.givenName"
      :short-family-name="message.shortFamilyName"
      :date="message.date"
      :id-message="message.idMessage"
      :id-recipient="message.idRecipient"
      :selected-default="message.selected"
      :origin="message.carpoolInfos.origin"
      :destination="message.carpoolInfos.destination"
      :criteria="message.carpoolInfos.criteria"
      :id-ask="message.idAsk"
      :id-ask-selected="idAskSelected"
      :blocker-id="message.blockerId"
      @idMessageForTimeLine="emit"
      @toggleSelected="emitToggle"
    />
    <v-skeleton-loader
      ref="skeleton"
      :boilerplate="boilerplate"
      :type="type"
      :tile="tile"
      class="mx-auto"
      :hidden="SkeletonHidden"
    />
  </v-main>
</template>
<script>

import { merge } from "lodash";
import axios from "axios";
import moment from "moment";
import {messages_fr, messages_en} from "@translations/components/user/mailbox/ThreadsCarpool/";
import {messages_client_fr, messages_client_en} from "@clientTranslations/components/user/mailbox/ThreadsCarpool/";
import ThreadCarpool from '@components/user/mailbox/ThreadCarpool'

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);

export default {
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'fr': MessagesMergedFr
    }
  },
  components:{
    ThreadCarpool
  },
  props: {
    idThreadDefault:{
      type: Number,
      default:null
    },
    newThread:{
      type:Object,
      default:null
    },
    idAskToSelect:{
      type: Number,
      default: null
    },
    refreshThreads: {
      type: Boolean,
      default: false
    }
  },
  data(){
    return{
      locale: this.$i18n.locale,
      messages:[],
      boilerplate: false,
      tile: false,
      type: 'list-item-avatar-three-line',
      types: [],
      SkeletonHidden: false,
      idAskSelected: this.idAskToSelect
    }
  },
  watch: {
    idAskToSelect() {
      this.idAskToSelect ? this.refreshSelected(this.idAskToSelect) : '';
    },
    refreshThreads(){
      (this.refreshThreads) ? this.getThreads(this.idMessageToSelect) : '';
    }
  },
  mounted(){
    this.getThreads(this.idAskToSelect);
  },
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
  },
  methods:{
    emit(data){
      this.$emit("idMessageForTimeLine",data);
    },
    emitToggle(data){
      this.$emit("toggleSelected",data);
    },
    refreshSelected(idAsk){
      this.messages.forEach((item, index) => {
        if(item.idAsk === idAsk){
          this.$set(item, 'selected', true);
        }
        else{
          this.$set(item, 'selected', false);
        }
      })
    },
    getThreads(idMessageSelected=null){
      this.SkeletonHidden = false;
      axios.get(this.$t("urlGet"))
        .then(response => {
          this.SkeletonHidden = true;
          this.messages = response.data.threads;
          // I'm pushing the new "virtual" thread
          if(this.newThread){
            response.data.threads.push({
              date:moment().format(),
              shortFamilyName:this.newThread.shortFamilyName,
              givenName:this.newThread.givenName,
              idMessage:-1,
              idRecipient:this.newThread.idRecipient
            });
          }
          (idMessageSelected) ? this.refreshSelected(idMessageSelected) : '';
          this.$emit("refreshThreadsCarpoolCompleted");
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    name(givenName, shortFamilyName) {
      return givenName + " " + shortFamilyName;
    }
  }
}
</script>
