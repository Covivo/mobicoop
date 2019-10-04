<template>
  <v-content>
    <!--SnackBar-->

    <v-snackbar
      v-model="snackbar"
      :color="(errorUpdate)?'error':'warning'"
      top
    >
      {{ (errorUpdate)?textSnackError:textSnackOk }}
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
      <community-infos
        :community="community"
        :url-alt-avatar="urlAltAvatar"
        :avatar-version="avatarVersion"
      />

      <!-- community buttons and map -->
      <v-row
        justify="center"
      >
        <v-col
          cols="12"
          md="8"
          xl="6"
          align="center"
        >
          <!-- Community : avatar, title and description -->
          <community-infos
            :cover-image="coverImage"
            :community="community"
          />

          <!-- community buttons and map -->
          <v-row
            align="center"
          >
            <v-col
              cols="4"
              class="text-center"
            >
              <div v-if="isMember && isAccepted">
                <a
                  style="text-decoration:none;"
                  :href="$t('buttons.publish.route')+community.id"
                >
                  <v-btn
                    color="success"
                    rounded
                  >
                    {{ $t('buttons.publish.label') }}
                  </v-btn>
                </a>
              </div>
              <div v-else-if="isMember == true">
                <v-tooltip
                  top
                  color="info"
                >
                  <template v-slot:activator="{ on }">
                    <a
                      style="text-decoration:none;"
                      :href="$t('buttons.publish.route')+community.id"
                      v-on="on"
                    >
                      <v-btn
                        color="success"
                        rounded
                        disabled
                      >
                        {{ $t('buttons.publish.label') }}
                      </v-btn>
                    </a>
                  </template>
                  <span>{{ $t('tooltips.validation') }}</span>
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
                        :loading="loading || (checkValidation && isLogged) "
                        :disabled="!isLogged || checkValidation"
                        @click="joinCommunity"
                      >
                        {{ $t('buttons.join.label') }}
                      </v-btn>
                    </a>
                  </template>
                  <span>{{ $t('tooltips.connected') }}</span>
                </v-tooltip>
              </div>
            </v-col>
            <!-- map -->
            <v-col
              cols="8"
            >
              <v-card
                v-show="loadingMap"
                flat
                align="center"
                height="500"
                color="backSpiner"
              >
                <v-progress-circular
                  size="497"
                  indeterminate
                  color="tertiary"
                />
              </v-card>
              <m-map
                v-show="!loadingMap"
                ref="mmap"
                type-map="community"
                :points="pointsToMap"
              />
            </v-col>
          </v-row>

          <!-- community members list + last 3 users -->
          <v-row 
            align="start"
          >
            <v-col
              cols="8"
            >
              <community-member-list
                :community="community"
              />
            </v-col>
            <!-- last 3 users -->
            <v-col
              cols="4"
            >
              <community-last-users
                :community="community"
              />
            </v-col>
          </v-row>
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
            {{ $t('title.searchCarpool') }}
          </p>
        </v-col>
      </v-row>
      <v-row
        align="center"
        justify="center"
      >
        <home-search
          :geo-search-url="geodata.geocompleteuri"
          :route="geodata.searchroute"
          :user="user"
          :justsearch="false"
          :notitle="true"
          :is-member="isMember"
          :community="community"
          :temporary-tooltips="true"
        />
      </v-row>
    </v-container>
  </v-content>
</template>
<script>

import axios from "axios";
import { merge } from "lodash";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/community/Community.json";
import TranslationsClient from "@clientTranslations/components/community/Community.json";
import CommunityMemberList from "@components/community/CommunityMemberList";
import CommunityInfos from "@components/community/CommunityInfos";
import HomeSearch from "@components/home/HomeSearch";
import CommunityLastUsers from "@components/community/CommunityLastUsers";
import MMap from "@components/utilities/MMap"
import L from "leaflet";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  components: {
    CommunityMemberList, CommunityInfos, HomeSearch, MMap, CommunityLastUsers
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
    lastUsers: {
      type: Array,
      default: null
    },
    avatarVersion: {
      type: String,
      default: null
    },
    urlAltAvatar: {
      type: String,
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
      ],
      pointsToMap:[],
      directionWay:[],
      loading: false,
      snackbar: false,
      textSnackOk: this.$t("snackbar.joinCommunity.textOk"),
      textSnackError: this.$t("snackbar.joinCommunity.textError"),
      errorUpdate: false,
      isAccepted: false,
      isMember: false,
      checkValidation: false,
      isLogged: false,
      loadingMap: false,

    }
  },
  mounted() {
    this.getCommunityUser();
    this.checkIfUserLogged();
    this.getCommunityProposals();
  },
  methods:{
    getCommunityUser() {
      this.checkValidation = true;
      axios 
        .get('/community-user/'+this.community.id, {
          headers:{
            'content-type': 'application/json'
          }
        })
        .then(res => {
          if (res.data.length > 0) {
            this.isAccepted = res.data[0].status == 1;
            this.isMember = true
          }
          this.checkValidation = false;
          
        });
    },
    joinCommunity() {
      this.loading = true;
      axios 
        .post(this.$t('buttons.join.route')+this.community.id,
          {
            headers:{
              'content-type': 'application/json'
            }
          })
        .then(res => {
          this.errorUpdate = res.data.state;
          this.snackbar = true;
          this.loading = false;
          this.isMember = true;
        });
    },
    checkIfUserLogged() {
      if (this.user !== null) {
        this.isLogged = true;
      }
    },
    getCommunityProposals () {
      this.loadingMap = true;
      axios 
       
        .get('/community-proposals/'+this.community.id,
          {
            headers:{
              'content-type': 'application/json'
            }
          })
        .then(res => {
          this.errorUpdate = res.data.state;
          this.pointsToMap.length = 0;
          // add the community address to display on the map
          if (this.community.address) {
            this.pointsToMap.push(this.buildPoint(this.community.address.latitude,this.community.address.longitude,this.community.name));
          }
          // add all the waypoints of the community to display on the map
          res.data.forEach((waypoint, index) => {
            this.pointsToMap.push(this.buildPoint(waypoint.latLng.lat,waypoint.latLng.lon,waypoint.title));
          });
          this.loadingMap = false;
          setTimeout(this.$refs.mmap.redrawMap(),600);
          
        });
    },
   
    buildPoint: function(lat,lng,title="",pictoUrl="",size=[],anchor=[]){
      let point = {
        title:title,
        latLng:L.latLng(lat, lng),
        icon: {}
      }

      if(pictoUrl!==""){
        point.icon = {
          url:pictoUrl,
          size:size,
          anchor:anchor
        }
      }
        
      return point;      
    }     

  }
}
</script>
