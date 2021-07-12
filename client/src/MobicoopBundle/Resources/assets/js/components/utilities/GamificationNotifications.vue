<template>
  <div>
    <v-snackbar
      v-for="rewardStep in rewardSteps"
      :key="rewardStep.id"
      v-model="snackbar"
      timeout="-1"
    >
      {{ $t("gamification") }} : {{ $t(rewardStep.title) }}

      <template v-slot:action="{ attrs }">
        <v-btn
          text
          v-bind="attrs"
          @click="snackbar = false"
        >
          <v-icon
            color="primary"
          >
            mdi-close
          </v-icon>
        </v-btn>
      </template>
    </v-snackbar>
  </div>
</template>
<script>
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/gamification/GamificationNotifications/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    },
  },
  data () {
    return {
      snackbar: false,
    }
  },
  computed:{
    gamificationNotifications(){
      return this.$store.getters['gn/gamificationNotifications'];
    },
    rewardSteps(){
      return this.gamificationNotifications.filter( item => item.type == "RewardStep" );
    }
  },
  watch:{
    gamificationNotifications(newVersion, oldVersion){
      this.snackbar = true;
    }
  },
  mounted(){
    if(localStorage.getItem("gamificationNotifications")){
      this.$store.commit('gn/updateGamificationNotifications',JSON.parse(localStorage.getItem("gamificationNotifications")));
      localStorage.removeItem("gamificationNotifications");
    }
  }
}
</script>