<template>
  <v-container fluid>
    <v-row>
      <v-col cols="12">
        <h2>{{ $t("badgesEarned.title") }}</h2>
      </v-col>
    </v-row>
    <v-row v-if="badges && badgesEarned">
      <v-col cols="12">
        <v-img
          v-for="badgeEarned in badgesEarned"
          :key="badgeEarned.badgeSummary.badgeId"
          :src="badgeEarned.badgeSummary.decoratedIcon"
          max-width="50px"
        />
      </v-col>
    </v-row>
    <v-row>
      <v-col cols="12">
        <h2>{{ $t("badgesInProgress.title") }}</h2>
      </v-col>
    </v-row>
    <v-row v-if="badges && badgesInProgress">
      <v-col cols="12">
        <v-row
          v-for="badgeInProgress in badgesInProgress"
          :key="badgeInProgress.badgeSummary.badgeId"
          align="center"
        >
          <v-col
            cols="1"
          >
            <v-img
              :src="badgeInProgress.badgeSummary.icon"
              max-width="50px"
            />
          </v-col>
          <v-col
            cols="11"
            justify="left"
          >
            {{ badgeInProgress.badgeSummary.badgeTitle }}
          </v-col>
        </v-row>
      </v-col>
    </v-row>
    <v-row>
      <v-col cols="12">
        <h2>{{ $t("otherBadges.title") }}</h2>
      </v-col>
    </v-row>
    <v-row v-if="badges && otherBadges">
      <v-col cols="12">
        <v-row
          v-for="otherBadge in otherBadges"
          :key="otherBadge.badgeSummary.badgeId"
          align="center"
        >
          <v-col
            cols="1"
          >
            <v-img
              :src="otherBadge.badgeSummary.icon"
              max-width="50px"
            />
          </v-col>
          <v-col
            cols="11"
            justify="left"
          >
            {{ otherBadge.badgeSummary.badgeTitle }}
          </v-col>
        </v-row>
      </v-col>
    </v-row>
  </v-container>
</template>
<script>
import maxios from "@utils/maxios";
import {messages_en, messages_fr, messages_eu, messages_nl, messages_it, messages_de} from "@translations/components/user/profile/Badges/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu': messages_eu,
      'it': messages_it,
      'de': messages_de
    },
  },
  props:{
  },
  data(){
    return{
      badges: null
    }
  },
  computed:{
    badgesEarned(){
      return this.badges.filter( item => item.earned );
    },
    badgesInProgress(){
      return this.badges.filter( item => item.earningPercentage > 0 && !item.earned );
    },
    otherBadges(){
      return this.badges.filter( item => item.earningPercentage == 0 && !item.earned );
    }
  },
  mounted(){
    this.getBadgesBoard();
  },
  methods:{
    getBadgesBoard(){
      maxios
        .post(this.$t('getBadgesUrl'))
        .then(res => {
        //   console.log(res.data);
          this.badges = res.data.badges;
        })
        .catch(error => {
          window.location.reload();
        });
    }
  }
}
</script>      