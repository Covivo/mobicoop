<template>
  <v-container>
    <!-- Community : avatar, title and description -->
    <v-row
      align="center"
      justify="center"
    >
      <v-col cols="2">
        <community-infos
          :paths="paths"
        />
      </v-col>
      
      <v-col
        cols="4"
      >
        <v-card
          flat
          height="25vh"
        >
          <v-card-text>
            <h3 class="headline">
              {{ community['name'] }}
            </h3>
            <p class="body-1">
              {{ community['description'] }}
            </p>
            <p class="body-2">
              {{ community['fullDescription'] }}
            </p>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
          
    <!-- community buttons and map -->
    <v-row
      justify="center"
      align="center"
    >
      <v-col
        cols="2"
        class="text-center"
      >
        <div v-if="isMember">
          <a
            style="text-decoration:none;"
            href="/covoiturage/annonce/poster"
          >
            <v-btn
              color="success"
              rounded
            >
              Publier une annonce
            </v-btn>
          </a>
        </div>

        <div v-else>
          <a
            style="text-decoration:none;"
            href="#"
          >
            <v-btn
              color="success"
              rounded
            >
              Rejoindre la communauté
            </v-btn>
          </a>
        </div>
      </v-col>
    
      <v-col cols="4">
        <m-map
          ref="mmapRoute"
          type-map="adSummary"
          :points="pointsToMap"
          :ways="directionWay"
        />
      </v-col>
    </v-row>

    <!-- community members list -->
    <v-row 
      align="center"
      justify="center"
    >
      <v-col cols="4">
        <v-card
          flat
        >
          <member-list :users="users" />
        </v-card>
      </v-col>
      <v-col cols="2">
        <v-toolbar
          flat
        >
          <v-toolbar-title class="headline">
            Ils nous ont rejoints
          </v-toolbar-title>
        </v-toolbar>
        <v-card
          flat
        >
          <v-list shaped>
            <v-list-item-group>
              <v-list-item
                v-for="(comUser, i) in lastUsers"
                :key="i"
              >
                <v-list-item-avatar>
                  <v-avatar color="tertiary">
                    <v-icon light>
                      mdi-account-circle
                    </v-icon>
                  </v-avatar>
                </v-list-item-avatar>
                <v-list-item-content>
                  <v-list-item-title v-text="comUser.user.givenName" />
                  <v-list-item-content v-text="comUser.acceptedDate" />
                </v-list-item-content>
              </v-list-item>
            </v-list-item-group>
          </v-list>
        </v-card>
      </v-col>
    </v-row>

    <!-- search journey -->
    <v-row
      justify="center"
      align="center"
    >
      <v-col
        cols="6"
        class="mt-6"
      >
        <p class="headline">
          Chercher un trajet dans la communauté
        </p>
      </v-col>
    </v-row>
    <v-row
      align="center"
      justify="center"
    >
      <v-col>
        <home-search
          :geo-search-url="geodata.geocompleteuri"
          :route="geodata.searchroute"
          :user="user"
          :justsearch="false"
          :notitle="true"
          :is-member="isMember"
        />
      </v-col>
    </v-row>
  </v-container>
</template>
<script>

import moment from "moment";
import { merge } from "lodash";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/community/CommunityDisplay.json";
import TranslationsClient from "@clientTranslations/components/community/CommunityDisplay.json";
import MemberList from "@components/community/MemberList";
import CommunityInfos from "@components/community/CommunityInfos";
import HomeSearch from "@components/home/HomeSearch";
import MMap from "@components/base/MMap"

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  components: {
    MemberList, CommunityInfos, HomeSearch, MMap,
  },
  i18n: {
    messages: TranslationsMerged,
    sharedMessages: CommonTranslations
  },
  props:{
    user: {
      type: Object,
      default: null
    },
    geodata: {
      type: Object,
      default: null
    },
    users: {
      type: Array,
      default: null
    },
    community:{
      type: Object,
      default: null
    },
    paths:{
      type: Object,
      default: null
    },
    isMember:{
      type: Boolean,
      default: false
    },
    lastUsers: {
      type: Array,
      default: null
    }
  },
  data () {
    return {
      statistics: [
        { text: 'Inscrits', number: 0 },
        { text: 'Offres de covoiturage', number: 0 },
        { text: 'Mise en relation', number: 0 },
        { text: 'Km covoiturés', number: 0 },
        { text: 'kg de CO2 consommés', number: 0 },
      ],
      search: '',
      headers: [
        {
          text: 'Id',
          align: 'left',
          sortable: false,
          value: 'id',
        },
        { text: 'Nom', value: 'familyName' },
        { text: 'Prenom', value: 'givenName' },
        { text: 'Telephone', value: 'telephone' },
        { text: 'Status', value: 'status' }
      ],
      pointsToMap:[],
      directionWay:[],

    }
  },
  computed: {
    // creation of the url to call
    urlToCall() {
      return `${this.baseUrl}/${this.route}/${this.origin.addressLocality}/${this.destination.addressLocality}/${this.origin.latitude}/${this.origin.longitude}/${this.destination.latitude}/${this.destination.longitude}/${this.computedDateFormat}/resultats`;
    },
    searchUnavailable() {
      return (!this.origin || !this.destination || this.loading == true)
    },
    computedDateFormat() {
      moment.locale(this.locale);
      return this.date
        ? moment(this.date).format(this.$t("ui.i18n.date.format.fullNumericDate"))
        : moment(new Date()).format(this.$t("ui.i18n.date.format.fullNumericDate"));
    },
    test() {
      for (let lastUser in lastUsers) {
        
      }
    }
  },
  methods:{
    linkToCommunityJoin: function (item) {
      return '/join-community/'+item.id;
    },
    linkToCommunityShow: function (item) {
      return '/community/'+item.id;
    },
    linkToCommunityWidget: function (item) {
      return '/community/show-widget/'+item.id;
    },
  }
}
</script>
