<template>
  <v-content>
    <v-card
      class="pa-2 text-center"
    >
      <!-- Always visible (carpool or not) -->
      <v-avatar v-if="infos.avatar && !loading">
        <img :src="infos.avatar">
      </v-avatar>
      <v-card-text
        v-if="!loading"
        class="font-weight-bold headline"
      >
        {{ infos.recipientName }}
      </v-card-text>

      <!-- Only visible for carpool -->
      <v-card
        v-if="idAskHistory && !loading"
        class="mb-3"
        flat
      >
        <threads-actions-buttons
          :user-id="idUser"
          :ask-user-id="infos.askUserId"
          @updateStatus="updateStatus"
        />
      </v-card>
      <v-card v-else-if="!loading">
        <v-card-text>
          {{ $t("notLinkedToACarpool") }}
        </v-card-text>
      </v-card>
      <v-skeleton-loader
        v-if="loading"
        ref="skeleton"
        type="card"
        class="mx-auto"
      />
      <v-skeleton-loader
        v-if="loading"
        ref="skeleton"
        type="actions"
        class="mx-auto"
      />
    </v-card>
  </v-content>
</template>
<script>
import Translations from "@translations/components/user/mailbox/ThreadActions.json";
import ThreadsActionsButtons from '@components/user/mailbox/ThreadsActionsButtons'
import axios from "axios";

export default {
  i18n: {
    messages: Translations,
  },
  components:{
    ThreadsActionsButtons
  },
  props: {
    idAskHistory: {
      type: Number,
      default: null
    },
    idUser: {
      type: Number,
      default: null
    },
    idRecipient: {
      type: Number,
      default: null
    },
    loadingInit: {
      type: Boolean,
      default: false
    },
    refresh: {
      type: Boolean,
      default: false
    }
  },
  data(){
    return{
      loading:this.loadingInit,
      recipientName:"",
      infos:[]
    }
  },
  watch:{
    idAskHistory(){
      this.refreshInfos();
    },
    loadingInit(){
      this.loading = this.loadingInit;
    },
    refresh(){
      if(this.refresh){
        this.refreshInfos();
      }
    }
  },
  methods:{
    refreshInfos(){
      this.loading = true;
      let params = {
        idAskHistory:this.idAskHistory,
        idRecipient:this.idRecipient
      }
      axios.post(this.$t("urlGetAskHistory"),params)
        .then(response => {
          //console.error(response.data);
          this.infos = response.data;
        })
        .catch(function (error) {
          console.log(error);
        })
        .finally(()=>{
          this.$emit("refreshActionsCompleted");
        });
    },
    updateStatus(data){
      this.$emit("updateStatusAskHistory",data);
    }
  }
}
</script>