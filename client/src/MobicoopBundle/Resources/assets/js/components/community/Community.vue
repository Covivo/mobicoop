<template>
  <div>
    <!--SnackBar-->

    <v-snackbar
      v-model="snackbar"
      :color="(errorUpdate)?'error': (community.validationType == 1 ? 'warning' : 'success')"
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
          lg="9"
          md="10"
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
              <m-map
                ref="mmap"
                type-map="community"
                :points="pointsToMap"
                :ways="directionWay"
                :provider="mapProvider"
                :url-tiles="urlTiles"
                :attribution-copyright="attributionCopyright"
                :markers-draggable="false"
                class="pa-4 mt-5"
              />
            </v-col>
          </v-row>

          <!-- community members list + last 3 users -->
          <v-row
            v-if="isLogged && isAccepted"
            align="start"
          >
            <v-col
              cols="8"
            >
              <community-member-list
                :community="community"
                :refresh="refreshMemberList"
                :given-users="users"
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
                :given-last-users="lastUsers"
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
        align="left"
      >
        <v-col
          cols="12"
          lg="9"
          md="10"
          xl="6"
          align="center"
          class="mt-6"
        >
          <h3 class="headline text-justify font-weight-bold">
            {{ $t('title.searchCarpool') }}
          </h3>
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
  </div>
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
    points: {
      type: Array,
      default: null
    }
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
      ],
      pointsToMap:[],
      directionWay:[],
      loading: false,
      snackbar: false,
      textSnackOk: this.community.validationType == 1 ? this.$t("snackbar.joinCommunity.textOkManualValidation") : this.$t("snackbar.joinCommunity.textOkAutoValidation"),
      textSnackError: this.$t("snackbar.joinCommunity.textError"),
      errorUpdate: false,
      isAccepted: false,
      askToJoin: false,
      checkValidation: false,
      isLogged: false,
      domain: true,
      refreshMemberList: false,
      refreshLastUsers: false,
      params: { 'communityId' : this.community.id },

    }
  },
  mounted() {
    this.getCommunityUser();
    this.checkIfUserLogged();
    this.showCommunityProposals();
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
              //accepted as user or moderator
              this.isAccepted = (res.data[0].status == 1 || res.data[0].status == 2);
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
    showCommunityProposals () {
      this.pointsToMap.length = 0;
      // add the community address to display on the map
      if (this.community.address) {
        this.pointsToMap.push(this.buildPoint(this.community.address.latitude,this.community.address.longitude,this.community.name));
      }
          
      // add all the waypoints of the community to display on the map
      // We draw straight lines between those points
      // if the user is already accepted or if the doesn't hide members or proposals to non members.
      if(this.isAccepted || (!this.community.membersHidden && !this.community.proposalsHidden) ){
        this.points.forEach((proposal, index) => {
          let currentProposal = {latLngs:[]};
          let infosForPopUp = {
            origin:'',
            destination:'',
            originLat:null,
            originLon:null,
            destinationLat:null,
            destinationLon:null,
            carpoolerFirstName:"",
            carpoolerLastName:""
          };

          if(proposal.type !== 'return'){ // We show only outward or one way proposals
            proposal.waypoints.forEach((waypoint, index) => {
              this.pointsToMap.push(this.buildPoint(waypoint.latLng.lat,waypoint.latLng.lon,waypoint.title));
              currentProposal.latLngs.push(waypoint.latLng);
              if(index==0){
                infosForPopUp.origin = waypoint.title;
                infosForPopUp.originLat = waypoint.latLng.lat;
                infosForPopUp.originLon = waypoint.latLng.lon;
              }
              else if(waypoint.destination){
                infosForPopUp.destination = waypoint.title;
                infosForPopUp.destinationLat = waypoint.latLng.lat;
                infosForPopUp.destinationLon = waypoint.latLng.lon;
              }
            });
            infosForPopUp.carpoolerFirstName = proposal.carpoolerFirstName;
            infosForPopUp.carpoolerLastName = proposal.carpoolerLastName;

            // We build the content of the popup
            currentProposal.desc = "<p><strong>"+infosForPopUp.carpoolerFirstName+" "+infosForPopUp.carpoolerLastName+"</strong></p>"
            currentProposal.desc += "<p style='text-align:left;'><strong>"+this.$t('map.origin')+"</strong> : "+infosForPopUp.origin+"<br />";
            currentProposal.desc += "<strong>"+this.$t('map.destination')+"</strong> : "+infosForPopUp.destination+"<br />";
            if(proposal.frequency=='regular') currentProposal.desc += "<em>"+this.$t('map.regular')+"</em>";

            // And now the content of a tooltip (same as popup but without the button)
            currentProposal.title = currentProposal.desc;
                
            // We add the button to the popup (To Do: Button isn't functionnal. Find a good way to launch a research)
            //currentProposal.desc += "<br /><button type='button' class='v-btn v-btn--contained v-btn--rounded theme--light v-size--small secondary overline'>"+this.$t('map.findMatchings')+"</button>";

            // We are closing the two p
            currentProposal.title += "</p>";
            currentProposal.desc += "</p>";
            this.directionWay.push(currentProposal);

          }
        });
      }
      this.$refs.mmap.redrawMap();
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
        givenName:data.givenName,
        avatar:data.avatars[0]
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
    },
    searchMatchings(){
      console.error("searchMatchings");
    }
  }
}
</script>

<style lang="scss" scoped>
.multiline {
  padding:20px;
  white-space: normal;
}
</style>
