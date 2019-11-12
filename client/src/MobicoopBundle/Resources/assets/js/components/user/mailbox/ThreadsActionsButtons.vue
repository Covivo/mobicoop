<template>
  <v-content>
    <!-- The Ask is just Initiated -->
    <!-- Only the Ask User can make a formal request of carpool -->
    <div v-if="status==1 && userId==askUserId">
      <v-tooltip
        bottom
        color="warning"
      >
        <template v-slot:activator="{ on }">
          <v-btn
            color="warning"
            fab
            large
            dark
            depressed
            v-on="on"
            @click="updateStatus(2)"
          >
            <v-icon class="display-2">
              mdi-car
            </v-icon>
          </v-btn> 
        </template>
        <span>{{ $t('askCarpool') }}</span>
      </v-tooltip>     
    </div>
    <div v-if="status==1 && userId!=askUserId">
      <v-card-text>{{ $t('onlyAskUser') }}</v-card-text>
    </div>
    <!-- end ask just Initiated -->


    <!-- The Ask is pending -->
    <!-- If you are the ask user you cannot accept or delined -->
    <div v-if="status==2 && userId != askUserId">
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
            @click="updateStatus(3)"
          >
            <v-icon class="display-2">
              mdi-check
            </v-icon>
          </v-btn> 
        </template>
        <span>{{ $t('accept') }}</span>
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
            @click="updateStatus(4)"
          >
            <v-icon class="display-2">
              mdi-close
            </v-icon>
          </v-btn>      
        </template>
        <span>{{ $t('refuse') }}</span>
      </v-tooltip>     
    </div>
    <div v-else-if="status==2">
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
    <div v-if="status==3">
      <v-card
        color="success"
        class="white--text"
        flat
      >
        {{ $t('askAccepted') }}
      </v-card>
    </div>
    <!-- The Ask is refused -->
    <div v-if="status==4">
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
    userId:{
      type:Number,
      default:0
    },
    askUserId:{
      type:Number,
      default:0
    }
  },
  methods:{
    updateStatus(status){
      this.$emit("updateStatus",{status:status});
    }
  }
}
</script>