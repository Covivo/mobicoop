<template>
  <v-content>
    <!-- The Ask is just Initiated -->
    <!-- Only the Askj User can make a formal request of carpool -->
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
    <div v-if="status==2">
      <!-- The Ask is pending -->
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