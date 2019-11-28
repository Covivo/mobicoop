<template>
  <v-content>
    <thread-carpool
      v-for="(message, index) in messages"
      :key="index"
      :avatar="message.avatarsRecipient"
      :given-name="message.givenName"
      :family-name="message.familyName"
      :date="message.date"
      :id-message="message.idMessage"
      :id-recipient="message.idRecipient"
      :selected-default="message.selected"
      :origin="message.carpoolInfos.origin"
      :destination="message.carpoolInfos.destination"
      :criteria="message.carpoolInfos.criteria"
      :id-ask="message.idAsk"
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
  </v-content>
</template>
<script>
import axios from "axios";
import moment from "moment";
import Translations from "@translations/components/user/mailbox/ThreadsCarpool.json";
import ThreadCarpool from '@components/user/mailbox/ThreadCarpool'
export default {
  i18n: {
    messages: Translations,
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
      messages:[],
      boilerplate: false,
      tile: false,
      type: 'list-item-avatar-three-line',
      types: [],
      SkeletonHidden: false
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
              familyName:this.newThread.familyName,
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
    name(givenName, familyName) {
      return givenName + " " + familyName.substr(0, 1).toUpperCase() + ".";
    }
  }
}
</script>
