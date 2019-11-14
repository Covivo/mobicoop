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
            :community="community"
            :url-alt-avatar="urlAltAvatar"
            :avatar-version="avatarVersion"
          />
          <!-- community buttons and map -->
          <v-row
            align="center"
          >
            <v-col
              cols="4"
              class="text-center"
            >
              <!-- button if domain validation -->
              <div
                v-if="domain == false"
              >
                <v-tooltip
                  left
                  color="info"
                >
                  <template v-slot:activator="{ on }">
                    <div
                      v-on="on"
                    >
                      <v-btn
                        rounded
                        disabled
                      >
                        {{ $t('buttons.join.label') }}
                      </v-btn>
                    </div>
                  </template>
                  <span>{{ $t('tooltips.domain')+" "+community.domain }}</span>
                </v-tooltip>
              </div>
              <!-- button if member is accepted -->
              <div v-else-if="isAccepted">
                <v-btn
                  color="secondary"
                  rounded
                  @click="publish"
                >
                  {{ $t('buttons.publish.label') }}
                </v-btn>
              </div>
              <!-- button if user ask to join community but is not accepted yet -->
              <div v-else-if="askToJoin == true">
                <v-tooltip
                  top
                  color="info"
                >
                  <template v-slot:activator="{ on }">
                    <a
                      style="text-decoration:none;"
                      :href="$t('buttons.publish.route', {communityId: community.id})"
                      v-on="on"
                    >
                      <v-btn
                        color="secondary"
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
              <!-- button is user is not a member -->
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
                        color="secondary"
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
                  size="250"
                  indeterminate
                  color="tertiary"
                />
              </v-card>
              <m-map
                v-show="!loadingMap"
                ref="mmap"
                type-map="community"
                :points="pointsToMap"
                :ways="directionWay"
                :provider="mapProvider"
                :url-tiles="urlTiles"
                :attribution-copyright="attributionCopyright"
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
                :refresh="refreshMemberList"
                :hidden="(!isAccepted && community.membersHidden)"
                @contact="contact"
                @refreshed="membersListRefreshed"
              />
            </v-col>
            <!-- last 3 users -->
            <v-col
              cols="4"
            >
              <community-last-users
                :refresh="refreshLastUsers"
                :community="community"
                :hidden="(!isAccepted && community.membersHidden)"
                @refreshed="lastUsersRefreshed"
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
        <search
          :geo-search-url="geodata.geocompleteuri"
          :user="user"
          :params="params"
          :punctual-date-optional="punctualDateOptional"
          :regular="regular"
        />
      </v-row>
    </v-container>
  </v-content>
</template>
<script>

import axios from "axios";
import { merge } from "lodash";
import Translations from "@translations/components/community/Community.json";
import TranslationsClient from "@clientTranslations/components/community/Community.json";
import CommunityMemberList from "@components/community/CommunityMemberList";
import CommunityInfos from "@components/community/CommunityInfos";
import Search from "@components/carpool/search/Search";
import CommunityLastUsers from "@components/community/CommunityLastUsers";
import MMap from "@components/utilities/MMap"
import L from "leaflet";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  components: {
    CommunityMemberList, CommunityInfos, Search, MMap, CommunityLastUsers
  },
  i18n: {
    messages: TranslationsMerged,
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
    },
    regular: {
      type: Boolean,
      default: false
    }, 
    punctualDateOptional: {
      type: Boolean,
      default: false
    },
    mapProvider:{
      type: String,
      default: ""
    },
    urlTiles:{
      type: String,
      default: ""
    },
    attributionCopyright:{
      type: String,
      default: ""
    },
  },
  data () {
    return {
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
      askToJoin: false,
      checkValidation: false,
      isLogged: false,
      loadingMap: false,
      domain: true,
      refreshMemberList: false,
      refreshLastUsers: false,
      params: { 'communityId' : this.community.id },

    }
  },
  mounted() {
    this.getCommunityUser();
    this.checkIfUserLogged();
    this.getCommunityProposals();
    this.checkDomain();
  },
  methods:{
    post: function (path, params, method='post') {
      const form = document.createElement('form');
      form.method = method;
      form.action = window.location.origin+'/'+path;

      for (const key in params) {
        if (params.hasOwnProperty(key)) {
          const hiddenField = document.createElement('input');
          hiddenField.type = 'hidden';
          hiddenField.name = key;
          hiddenField.value = params[key];
          form.appendChild(hiddenField);
        }
      }
      document.body.appendChild(form);
      form.submit();
    },
    getCommunityUser() {
      if(this.user){
        this.checkValidation = true;
        axios 
          .post(this.$t('urlCommunityUser'),{communityId:this.community.id, userId:this.user.id})
          .then(res => {
            if (res.data.length > 0) {
              this.isAccepted = res.data[0].status == 1;
              this.askToJoin = true
            }
            this.checkValidation = false;
            
          });
      }
    },
    joinCommunity() {
      this.loading = true;
      axios 
        .post(this.$t('buttons.join.route',{id:this.community.id}),
          {
            headers:{
              'content-type': 'application/json'
            }
          })
        .then(res => {
          this.errorUpdate = res.data.state;
          this.askToJoin = true;
          this.snackbar = true;
          this.refreshMemberList = true;
          this.refreshLastUsers = true;
          this.getCommunityUser();
          this.loading = false;
        });
    },
    checkIfUserLogged() {
      if (this.user !== null) {
        this.isLogged = true;
      }
    },
    checkDomain() {
      if (this.community.validationType == 2) {
        let mailDomain = (this.user.email.split("@"))[1];
        if (!(this.community.domain.includes(mailDomain))) {
          return this.domain = false;
        }   
      }
    },
    publish() {
      let lParams = {
        origin: null,
        destination: null,
        regular: null,
        date: null,
        time: null,
        ...this.params
      };
      this.post(`${this.$t("buttons.publish.route")}`, lParams);
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
          
          // add all the waypoints of the community to display on the map :
          // if the user is already accepted or if the doesn't hide members or proposals to non members.
          if(this.isAccepted || (!this.community.membersHidden && !this.community.proposalsHidden) ){
            console.error(res.data);
            res.data.forEach((proposal, index) => {
              let currentProposal = {latLngs:[]};
              proposal.forEach((waypoint, index) => {
                this.pointsToMap.push(this.buildPoint(waypoint.latLng.lat,waypoint.latLng.lon,waypoint.title));
                currentProposal.latLngs.push(waypoint.latLng);
              });
              this.directionWay.push(currentProposal);
            });
            console.error(this.directionWay);
          }
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
    },
    contact: function(data){
      const form = document.createElement('form');
      form.method = 'post';
      form.action = this.$t("buttons.contact.route");
      
      const params = {
        carpool:0,
        idRecipient:data.id,
        familyName:data.familyName,
        givenName:data.givenName
      }
      
      for (const key in params) {
        if (params.hasOwnProperty(key)) {
          const hiddenField = document.createElement('input');
          hiddenField.type = 'hidden';
          hiddenField.name = key;
          hiddenField.value = params[key];
          form.appendChild(hiddenField);
        }
      }
      document.body.appendChild(form);
      form.submit();      
    },
    membersListRefreshed(){
      this.refreshMemberList = false;
    },
    lastUsersRefreshed(){
      this.refreshLastUsers = false;
    }

  }
}
</script>
