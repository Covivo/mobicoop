<template>
  <v-content>
    <thread-carpool
      v-for="(message, index) in messages"
      :key="index"
      :given-name="message.givenName"
      :family-name="message.familyName"
      :date="message.date"
      :id-message="message.idMessage"
      :id-recipient="message.idRecipient"
      :selected-default="message.selected"
      :origin="message.carpoolInfos.origin"
      :destination="message.carpoolInfos.destination"
      :criteria="message.carpoolInfos.criteria"
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
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/user/mailbox/ThreadsCarpool.json";
import ThreadCarpool from '@components/user/mailbox/ThreadCarpool'
export default {
  i18n: {
    messages: Translations,
    sharedMessages: CommonTranslations
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
    idMessageToSelect:{
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
    idMessageToSelect(){
      (this.idMessageToSelect) ? this.refreshSelected(this.idMessageToSelect) : '';
    },
    refreshThreads(){
      (this.refreshThreads) ? this.getThreads(this.idMessageToSelect) : '';
    }
  },
  mounted(){
    this.getThreads(this.idThreadDefault);
  },
  methods:{
    emit(data){
      this.$emit("idMessageForTimeLine",data);
    },
    emitToggle(data){
      this.$emit("toggleSelected",data);
    },
    refreshSelected(idMessage){
      this.messages.forEach((item, index) => {
        if(item.idMessage == idMessage){
          this.$set(item, 'selected', true);
          // After the select we need to refresh the details
          this.emit(
            {
              idMessage:item.idMessage,
              idRecipient:item.idRecipient,
              name:this.name(item.givenName,item.familyName)
            }
          )
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
