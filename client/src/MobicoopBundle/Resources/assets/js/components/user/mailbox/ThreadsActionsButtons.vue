<template>
  <v-content>
    <!-- The Ask is just Initiated -->
    <!-- Only the Ask User can make a formal request of carpool -->
    <div v-if="status==1 && canUpdateAsk">
      <v-btn
        class="mb-2"
        color="primary"
        large
        dark
        rounded
        depressed
        :loading="loading"
        v-on="on"
        @click="updateStatus(2,'driver')"
      >
        {{ $t('button.askCarpoolAsDriver') }}
      </v-btn> 
       
      <v-btn
        color="primary"
        large
        dark
        rounded
        depressed
        :loading="loading"
        v-on="on"
        @click="updateStatus(3,'passenger')"
      >
        {{ $t('button.askCarpoolAsPassenger') }}
      </v-btn>
    </div>
    <div v-if="status==1 && !canUpdateAsk">
      <v-card-text>{{ $t('onlyAskUser') }}</v-card-text>
    </div>
    <!-- end ask just Initiated -->


    <!-- The Ask is pending -->
    <!-- If you are the ask user you cannot accept or delined -->
    <div v-if="(status==2 || status==3) && canUpdateAsk">
      <v-tooltip
        bottom
        color="success"
      >
        <template v-slot:activator="{ on }">
          <v-btn
            color="success"
            fab
            large
            dark
            depressed
            v-on="on"
            @click="updateStatus((status==2) ? 4 : 5)"
          >
            <v-icon class="display-2">
              mdi-check
            </v-icon>
          </v-btn> 
        </template>
        <span>{{ $t('button.accept') }}</span>
      </v-tooltip>     
      <v-tooltip
        bottom
        color="error"
      >
        <template v-slot:activator="{ on }">
          <v-btn
            color="error"
            fab
            large
            dark
            depressed
            v-on="on"
            @click="updateStatus((status==2) ? 6 : 7)"
          >
            <v-icon class="display-2">
              mdi-close
            </v-icon>
          </v-btn>      
        </template>
        <span>{{ $t('button.refuse') }}</span>
      </v-tooltip>     
    </div>
    <div v-else-if="(status==2 || status==3)">
      <v-card
        color="warning"
        class="white--text"
        flat
      >
        {{ $t('askPending') }}
      </v-card>
    </div>
    <!-- End the Ask is pending -->


    <!-- The Ask is accepted -->
    <div v-if="status==4 || status==5">
      <v-card
        color="success"
        class="white--text"
        flat
      >
        {{ $t('askAccepted') }}
      </v-card>
    </div>
    <!-- The Ask is refused -->
    <div v-if="status==6 || status==7">
      <v-card
        color="error"
        class="white--text"
        flat
      >
        {{ $t('askRefused') }}
      </v-card>
    </div>
  </v-content>
</template>
<script>
import Translations from "@translations/components/user/mailbox/ThreadsActionsButtons.json";
export default {
  i18n: {
    messages: Translations,
  },
  props:{
    status:{
      type:Number,
      default:1
    },
    canUpdateAsk:{
      type:Boolean,
      default:false
    },
    regular:{
      type:Boolean,
      default:false
    },
    loadingBtn:{
      type:Boolean,
      default:false
    },
    driver:{
      type:Boolean,
      default:false
    },
    passenger:{
      type:Boolean,
      default:false
    },
  },
  data(){
    return {
      loading:this.loadingBtn
    }
  },
  watch:{
    loadingBtn(){
      this.loading = this.loadingBtn
    }
  },
  methods:{
    updateStatus(status,role=null){
      this.$emit("updateStatus",{status:status,role:role});
    }
  }
}
</script>