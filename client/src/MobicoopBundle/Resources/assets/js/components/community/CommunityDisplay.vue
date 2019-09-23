<template>
  <v-content>
    <!--SnackBar-->

    <v-snackbar
      v-model="snackbar"
      :color="(this.errorUpdate)?'error':'warning'"
      top
    >
      {{ (this.errorUpdate)?this.textSnackError:this.textSnackOk }}
      <v-btn
        color="white"
        text
        @click="snackbar = false"
      >
        <v-icon>mdi-close-circle-outline</v-icon>
      </v-btn>
    </v-snackbar>

    <v-container>
      <!-- Community : avatar, title and description -->
      <v-row
        align="center"
        justify="center"
      >
        <v-col cols="2">
          <community-infos
            :cover-image="coverImage"
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
          <div v-if="isMember && isAccepted">
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
          <div v-else-if="isMember">
            <v-tooltip
              top
              color="info"
            >
              <template v-slot:activator="{ on }">
                <a
                  style="text-decoration:none;"
                  href="/covoiturage/annonce/poster"
                  v-on="on"
                >
                  <v-btn
                    color="success"
                    rounded
                    disabled
                  >
                    Publier une annonce
                  </v-btn>
                </a>
              </template>
              <span>En attente de validation</span>
            </v-tooltip>
          </div>
          <div
            v-else
          >
            <v-tooltip
              top
              color="info"
              :disabled="isLogged"
            >
              <template v-slot:activator="{ on }">
                <a
                  style="text-decoration:none;"
                  href="#"
                  v-on="on"
                >
               
                  <v-btn
                    color="success"
                    rounded
                    :loading="loading"
                    :disabled="!isLogged"
                    @click="joinCommunity"
                  >
                    Rejoindre la communauté
                  </v-btn>
                </a>
              </template>
              <span>Vous devez être connecté</span>
            </v-tooltip>
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

      <!-- community members list + last 3 users -->
      <v-row 
        align="start"
        justify="center"
      >
        <v-col
          cols="4"
          class="ml-5"
        >
          <member-list :users="users" />
        </v-col>
        <!-- last 3 users -->
        <v-col
          cols="2"
          class="mt-3 ps-12"
        >
          <v-toolbar
            flat
          >
            <v-toolbar-title class="headline">
              Ils nous ont rejoints
            </v-toolbar-title>
          </v-toolbar>
          <v-card
            flat
            class="mx-6"
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
                    <v-list-item-title v-text="comUser.name" />
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
  </v-content>
</template>
<script>

import axios from "axios";
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
    coverImage:{
      type: Object,
      default: null
    },
    lastUsers: {
      type: Array,
      default: null
    },
    isLogged: {
      type: Boolean,
      default: false
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
      loading: false,
      snackbar: false,
      textSnackOk: "Votre demande d'adhésion est bien envoyée au référent. Vous serez informé par email.",
      textSnackError: "Une erreur est survenue veillez essayer à nouveau",
      errorUpdate: false,
      isMember: false,
      isAccepted: false,

    }
  },
  mounted() {
    this.getCommunityUser();
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
    getCommunityUser() {
      axios 
        .get('/community-user/'+this.community.id, {
          headers:{
            'content-type': 'application/json'
          }
        })
        .then(res => {
          this.isMember = res.data !== null;
          this.isAccepted = res.data.status == 1;
          
        });
    },
    joinCommunity() {
      this.loading = true;
      axios 
        .post('/rejoindre-communaute/'+this.community.id,
          {
            headers:{
              'content-type': 'application/json'
            }
          })
        .then(res => {
          this.errorUpdate = res.data.state;
          this.snackbar = true;
          this.loading = false;
          document.location.reload(true);
          
        });
      
      return 1;
    }

  }
}
</script>
