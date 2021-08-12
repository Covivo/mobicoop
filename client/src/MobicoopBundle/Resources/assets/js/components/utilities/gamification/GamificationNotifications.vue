<template>
  <div>
    <v-snackbar
      v-model="snackbar"
      top
      timeout="-1"
      style="white-space: pre-line;"
    >
      {{ rewardStepsText }}

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
    <GamificationBadgesNotifications 
      :badges="badges"
    />
  </div>
</template>
<script>
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/gamification/GamificationNotifications/";
import GamificationBadgesNotifications from "@components/utilities/gamification/GamificationBadgesNotifications";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    },
  },
  components: {
    GamificationBadgesNotifications
  },
  props:{
    userGamificationNotifications:{
      type: Array,
      default: null
    }
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
    },
    rewardStepsText(){
      let text = [];
      this.rewardSteps.forEach((item, index) => {
        text.push(this.$t(item.title)+this.$t('nextABadge',{badgeName:item.badge.title}));
      });
      return text.join("\n ");
    },
    badges(){
      return this.gamificationNotifications.filter( item => item.type == "Badge" );
    }
  },
  watch:{
    gamificationNotifications(newVersion, oldVersion){
      this.snackbar = this.rewardSteps.length > 0 ? true : false;
    }
  },
  mounted(){
    if(localStorage.getItem("gamificationNotifications")){
      this.$store.commit('gn/updateGamificationNotifications',JSON.parse(localStorage.getItem("gamificationNotifications")));
      localStorage.removeItem("gamificationNotifications");
    }
    if(this.userGamificationNotifications){
      this.$store.commit('gn/updateGamificationNotifications',this.userGamificationNotifications);
    }
  }
}
</script>