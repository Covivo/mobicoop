<template>
  <v-container fluid>
    <v-row>
      <v-col cols="12">
        <h2>{{ $t("badgesEarned.title") }}</h2>
      </v-col>
    </v-row>
    <v-row v-if="loading">
      <v-col>
        <v-skeleton-loader
          class="mx-auto"
          max-width="100%"
          type="avatar"
        />
      </v-col>     
    </v-row>

    <v-row v-else-if="badges && badgesEarned">
      <v-col cols="12">
        <v-row v-if="badgesEarned.length>0">
          <v-col
            v-for="badgeEarned in badgesEarned"
            :key="badgeEarned.badgeSummary.badgeId"
            cols="2"
          >
            <v-row
              justify="center"
              dense
            >
              <v-col
                cols="12"
                align="center"
              >
                <v-img
                  :src="badgeEarned.badgeSummary.decoratedIcon"
                  max-width="100px"
                />
              </v-col>
            </v-row>
            <v-row
              justify="center"
              dense
            >
              <v-col
                cols="12"
                align="center"
              >
                {{ badgeEarned.badgeSummary.badgeTitle }}
              </v-col>
            </v-row>
          </v-col>
        </v-row>
        <v-row v-else>
          <v-col>{{ $t('badgesEarned.nobadges') }}.</v-col>
        </v-row>
      </v-col>
    </v-row>
    <v-row>
      <v-col cols="12">
        <h2>{{ $t("badgesInProgress.title") }}</h2>
      </v-col>
    </v-row>
    <v-row v-if="loading">
      <v-col>
        <v-skeleton-loader
          class="mx-auto"
          max-width="100%"
          type="list-item-avatar@3"
        />
      </v-col>
    </v-row>    
    <v-row v-else-if="badges && badgesInProgress && badgesInProgress.length>0">
      <v-col cols="12">
        <v-expansion-panels
          accordion
          flat
        >
          <v-expansion-panel
            v-for="badgeInProgress in badgesInProgress"
            :key="badgeInProgress.badgeSummary.badgeId"
            align="center"
          >
            <v-expansion-panel-header>
              <v-row
                align="center"
                no-gutters
              >
                <v-col
                  cols="2"
                  no-gutters
                  class="mr-0 text-right"
                >
                  <v-img
                    :src="badgeInProgress.badgeSummary.icon"
                    max-width="100px"
                  />
                </v-col>
                <v-col
                  cols="10"
                  justify="left"
                >
                  {{ badgeInProgress.badgeSummary.badgeTitle }}
                  <v-progress-linear
                    :value="badgeInProgress.earningPercentage"
                    color="primary"
                    height="25"
                  >
                    <template v-slot:default="{ value }">
                      <strong>{{ Math.ceil(value) }}%</strong>
                    </template>
                  </v-progress-linear>            
                </v-col>
              </v-row>
            </v-expansion-panel-header>
            <v-expansion-panel-content>
              <v-list dense>
                <v-list-item
                  v-for="(sequence, i) in badgeInProgress.badgeSummary.sequences"
                  :key="i"
                >
                  <v-list-item-icon>
                    <v-icon v-if="sequence.validated">
                      mdi-check
                    </v-icon>
                  </v-list-item-icon>
                  <v-list-item-content class="text-left">
                    <span
                      :style="(sequence.validated) ? 'font-weight:bold;' : ''"
                    >{{ sequence.title }}</span>
                  </v-list-item-content>
                </v-list-item>                
              </v-list>
            </v-expansion-panel-content>          
          </v-expansion-panel>
        </v-expansion-panels>
      </v-col>
    </v-row>
    <v-row v-else>
      <v-col>
        {{ $t('badgesInProgress.nobadges') }}.
      </v-col>
    </v-row>
    <v-row>
      <v-col cols="12">
        <h2>{{ $t("otherBadges.title") }}</h2>
      </v-col>
    </v-row>
    <v-row v-if="loading">
      <v-col>
        <v-skeleton-loader
          class="mx-auto"
          max-width="100%"
          type="list-item-avatar@3"
        />
      </v-col>
    </v-row>
    <v-row v-else-if="badges && otherBadges && otherBadges.length>0">
      <v-col
        v-for="otherBadge in otherBadges"
        :key="otherBadge.badgeSummary.badgeId"
        cols="6"
      >
        <v-row
          align="center"
          dense
        >
          <v-col
            cols="3"
          >
            <v-img
              :src="otherBadge.badgeSummary.icon"
              max-width="100px"
            />
          </v-col>
          <v-col
            cols="9"
            justify="left"
          >
            {{ otherBadge.badgeSummary.badgeTitle }}
          </v-col>
        </v-row>
      </v-col>
    </v-row>
    <v-row v-else>
      <v-col>{{ $t('otherBadges.nobadges') }}.</v-col>
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
      badges: null,
      loading:true
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
      this.loading = true;
      maxios
        .post(this.$t('getBadgesUrl'))
        .then(res => {
        //   console.log(res.data);
          this.badges = res.data.badges;
          this.loading = false;
        });
    }
  }
}
</script>      