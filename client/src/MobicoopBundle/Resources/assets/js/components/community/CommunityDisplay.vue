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
          color="primary"
        >
          <v-card-text>
            <p class="display-1">
              {{ community['name'] }}
            </p>
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
    >
      <v-col
        cols="2"
        class="text-center"
      >
        <div class="my-2 mb-12">
          <a 
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
       
        <div class="mt-12">
          <a
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
          class="ma-3 pa-6"
          outlined
          tile
        >
          <member-list :users="users" />
        </v-card>
      </v-col>
      <v-col cols="2">
        <v-toolbar
          class="ma-3"
          style="margin-bottom: 0!important;"
        >
          <v-toolbar-title>ils nous ont rejoints</v-toolbar-title>
        </v-toolbar>
        <v-card
          class="ma-3 pa-6"
          outlined
          tile
          style="margin-top: 0!important;"
        >
          <v-list shaped>
            <v-list-item-group>
              <v-list-item
                v-for="(aSignupUser, i) in signUpUsers"
                :key="i"
              >
                <v-list-item-icon>
                  <v-badge
                    left
                    style="margin-right: 50px;"
                  >
                    <template v-slot:badge>
                      <img
                        :src="aSignupUser.avatar"
                        alt="no_avatar"
                      >
                    </template>
                  </v-badge>
                </v-list-item-icon>
                <v-list-item-content>
                  <v-list-item-title v-text="aSignupUser.name" />
                  <v-list-item-content v-text="aSignupUser.acceptedDate" />
                </v-list-item-content>
              </v-list-item>
            </v-list-item-group>
          </v-list>
        </v-card>
      </v-col>
    </v-row>

    <!-- search journey -->
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
import L from "leaflet";

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
    signUpUsers: {
      type: Array,
      default: function(){
        return [{avatar: '_', name: 'Ngouffo Doric', acceptedDate: '12/01/2019'}]
      }
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
      actualities:[
        {'id': 1, 'title': "L'application V2 est bientot la"},
        {'id': 2, 'title': "La nouvelle fonctionalité: la preuve du covoiturage"}
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
    }
  }
}
</script>
