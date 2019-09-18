<template>
  <div class="margin">
    <div id="community_headers">
      <v-row>
        <v-col
          class="col-2"
        >
          <v-row>
            <v-col cols="12">
              <community-infos
                :community="community"
                :paths="paths"
              />
            </v-col>
            <v-col cols="12">
              <div
                v-if="!community.isMembersHidden && !community.isProposalsHidden"
                class="my-2"
              >
                <a :href="linkToCommunityWidget(community)">
                  <v-btn color="primary">
                    Afficher le widget
                  </v-btn>
                </a>
              </div>
              <div
                v-if="community.isMembersHidden || community.isProposalsHidden"
                class="my-2"
              >
                <a :href="linkToCommunityJoin(item)">
                  <v-btn color="primary">
                    Rejoindre la communauté
                  </v-btn>
                </a>
              </div>

              <div
                class="my-2"
              >
                <a href="/covoiturage/annonce/poster">
                  <v-btn color="primary">
                    Publier une annonce
                  </v-btn>
                </a>
              </div>
            </v-col>
          </v-row>
        </v-col>
        <v-col
          class="col-10"
        >
          <v-card>
            <v-container>
              <v-row>
                <p>{{ community['name'] }}</p>
              </v-row>
              <v-row>
                <p>{{ community['description'] }}</p>
              </v-row>
              <v-row>
                <p>{{ community['fullDescription'] }}</p>
              </v-row>
              <v-card height="300px">
                Carte de la communauté avec l'ensemble des membres
              </v-card>
            </v-container>
          </v-card>
        </v-col>
      </v-row>
    </div>
    <div id="community_body">
      <v-row>
        <v-col cols="12">
          <v-card
            class="ma-3 pa-6"
            outlined
            tile
          >
            <member-list :users="users" />
          </v-card>
        </v-col>
      </v-row>
      <v-container fluid>
        <v-row>
          <v-col cols="12">
            <v-row
              class="grey lighten-5"
              style="height: 500px;"
            >
              <v-col cols="4">
                <v-toolbar
                  class="ma-3"
                  style="margin-bottom: 0!important;"
                >
                  <v-toolbar-title>La communauté c'est</v-toolbar-title>
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
                        v-for="(statistic, i) in statistics"
                        :key="i"
                      >
                        <v-list-item-icon>
                          <v-badge left>
                            <template v-slot:badge>
                              <span v-text="statistic.number" />
                            </template>
                          </v-badge>
                        </v-list-item-icon>
                        <v-list-item-content>
                          <v-list-item-title v-text="statistic.text" />
                        </v-list-item-content>
                      </v-list-item>
                    </v-list-item-group>
                  </v-list>
                </v-card>
              </v-col>
              <v-col cols="4">
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
              <v-col cols="4">
                <v-toolbar
                  class="ma-3"
                  style="margin-bottom: 0!important;"
                >
                  <v-toolbar-title>Actualités</v-toolbar-title>
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
                        v-for="(actuality, i) in actualities"
                        :key="i"
                      >
                        <v-list-item-content>
                          <v-list-item-title v-text="actuality.title" />
                          <v-list-item-content style="text-align: right">
                            <a
                              :href="'/actuality/'+actuality.id"
                            >{{ $t('ui.read.more') }}</a>
                          </v-list-item-content>
                        </v-list-item-content>
                      </v-list-item>
                    </v-list-item-group>
                  </v-list>
                </v-card>
              </v-col>
            </v-row>
          </v-col>
        </v-row>
      </v-container>
    </div>
    <div id="community_footer">
      <v-toolbar
        style="margin-bottom: 0!important;"
      >
        <v-toolbar-title>Trouver un trajet dans la communauté {{ community.name }}</v-toolbar-title>
      </v-toolbar>
      <v-card>
        <home-search
          :geo-search-url="geodata.geocompleteuri"
          :route="geodata.searchroute"
          :user="user"
          :justsearch="false"
          :notitle="true"
          :notembedded="false"
        />
      </v-card>
    </div>
  </div>
</template>
<script>

import moment from "moment";
import { merge } from "lodash";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/community/CommunityDisplay.json";
import TranslationsClient from "@clientTranslations/components/community/CommunityDisplay.json";
import MemberList from "./MemberList";
import CommunityInfos from "./CommunityInfos";
import HomeSearch from "../home/HomeSearch";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  components: {
    MemberList,CommunityInfos,HomeSearch
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
      ]
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
