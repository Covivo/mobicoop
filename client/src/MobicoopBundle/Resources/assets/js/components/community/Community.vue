<template>
  <div>
    <!--SnackBar-->

    <v-snackbar
      v-model="snackbar"
      :color="
        errorUpdate
          ? 'error'
          : community.validationType == 1
            ? 'warning'
            : 'success'
      "
      top
    >
      {{ textSnackbar }}
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
      <v-row justify="center">
        <v-col
          cols="12"
          lg="9"
          md="10"
          xl="6"
        >
          <!-- Community : avatar, title and description -->
          <community-infos
            :community="community"
            :url-alt-avatar="urlAltAvatar"
            :avatar-version="avatarVersion"
          />
          <p>{{ user && user.isCommunityReferrer }}</p>
          <!-- community buttons and map -->
          <v-row>
            <v-col
              cols="4"
              class="text-center"
            >
              <!-- button if domain validation -->
              <div v-if="domain == false && isSecured == false">
                <v-tooltip
                  left
                  color="info"
                >
                  <template v-slot:activator="{ on }">
                    <div v-on="on">
                      <v-btn
                        rounded
                        disabled
                        @click="joinCommunityDialog = true"
                      >
                        {{ $t("buttons.join.label") }}
                      </v-btn>
                    </div>
                  </template>
                  <span>{{
                    $t("tooltips.domain") + " " + community.domain
                  }}</span>
                </v-tooltip>
              </div>
              <!-- button if member is accepted -->
              <div v-else-if="isAccepted">
                <v-btn
                  color="secondary"
                  rounded
                  :width="250"
                  :loading="loading"
                  @click="publish"
                >
                  {{ $t("buttons.publish.label") }}
                </v-btn>
                <!-- button for access to the admin : only for creator -->
                <div v-if="canAdmin && canAccessAdminFromCommunity !== false">
                  <v-btn
                    class="mt-5"
                    color="secondary"
                    style="letter-spacing: -0.01px;"
                    rounded
                    target="_blank"
                    :href="urlAdmin"
                    :width="250"
                  >
                    {{ $t("buttons.accessAdmin.label") }}
                  </v-btn>
                </div>
                <v-btn
                  class="mt-5"
                  color="primary"
                  rounded
                  :width="250"
                  :loading="loading"
                  :disabled="!isLogged"
                  @click="leaveCommunityDialog = true"
                >
                  {{ $t("leaveCommunity.button") }}
                </v-btn>
              </div>
              <!-- button if user asked to join community but is not accepted yet -->
              <div v-else-if="askToJoin === true && !isAccepted">
                <v-tooltip
                  top
                  color="info"
                >
                  <template v-slot:activator="{ on }">
                    <a
                      style="text-decoration:none;"
                      :href="$t('buttons.publish.route')"
                      v-on="on"
                    >
                      <v-btn
                        color="secondary"
                        rounded
                        :disabled="(!publishButtonAlwaysActive && !checkValidation) || !isAccepted"
                        :loading="loading"
                      >
                        {{ $t("buttons.publish.label") }}
                      </v-btn>
                    </a>
                    <v-btn
                      class="mt-3"
                      color="primary"
                      rounded
                      :loading="loading"
                      :disabled="!isLogged"
                      @click="leaveCommunityDialog = true"
                    >
                      {{ $t("leaveCommunity.button") }}
                    </v-btn>
                  </template>
                  <span>{{ $t("tooltips.validation") }}</span>
                </v-tooltip>
              </div>
              <!-- button if user is not a member -->
              <div v-else>
                <v-tooltip
                  top
                  color="info"
                  :disabled="isLogged"
                >
                  <template v-slot:activator="{ on, attrs }">
                    <div
                      v-bind="attrs"
                      v-on="on"                    
                    >
                      <v-btn
                        color="secondary"
                        rounded
                        :disabled="(!publishButtonAlwaysActive && !checkValidation) || !isLogged ||!isAccepted"
                        :loading="loading"
                        @click="publish"
                      >
                        {{ $t("buttons.publish.label") }}
                      </v-btn>
                    </div>
                  </template>
                  <span>{{ $t("tooltips.connected") }}</span>
                </v-tooltip>
                <v-tooltip
                  top
                  color="info"
                  :disabled="isLogged"
                >
                  <template v-slot:activator="{ on, attrs }">
                    <div
                      v-bind="attrs"
                      v-on="on"                    
                    >
                      <v-btn
                        v-if="isSecured == false"
                        color="secondary"
                        class="mt-3"
                        rounded
                        :loading="loading || (checkValidation && isLogged)"
                        :disabled="!isLogged || checkValidation"
                        @click="joinCommunityDialog = true"
                      >
                        {{ $t("buttons.join.label") }}
                      </v-btn>
                    </div>
                  </template>
                  <span>{{ $t("tooltips.connected") }}</span>
                </v-tooltip>
              </div>

              <!-- widget -->
              <v-btn
                class="mt-5"
                color="primary"
                rounded
                :href="$t('widget.route', { id: community.id })"
              >
                {{ $t("widget.label") }}
              </v-btn>
            </v-col>
            <!-- map -->
            <v-col cols="8">
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
                :relay-points="true"
                @SelectedAsDestination="selectedAsDestination"
                @SelectedAsOrigin="selectedAsOrigin"
              />
            </v-col>
          </v-row>
          
          <!-- community members list + last 3 users -->
          <v-row
            v-if="isLogged && isAccepted && !loading"
            align="start"
          >
            <v-col cols="8">
              <community-member-list
                :community-id="community.id"
                :refresh="refreshMemberList"
                :given-users="users"
                :hidden="!isAccepted && community.membersHidden"
                :direct-message="directMessage"
                @contact="contact"
                @refreshed="membersListRefreshed"
              />
            </v-col>
            <!-- last 3 users -->
            <v-col cols="4">
              <community-last-users
                :refresh="refreshLastUsers"
                :community="community"
                :given-last-users="lastUsers"
                :hidden="!isAccepted && community.membersHidden"
                @refreshed="lastUsersRefreshed"
              />
            </v-col>
          </v-row>
          <v-row v-else-if="loading">
            <v-col cols="9">
              <v-skeleton-loader
                class="mx-auto"
                type="card"
              />
            </v-col>
            <v-col cols="3">
              <v-skeleton-loader
                class="mx-auto"
                type="card"
              />
            </v-col>
          </v-row>
        </v-col>
      </v-row>
      <!-- search journey -->
      <v-row 
        v-if="isAccepted"
        justify="center"
      >
        <v-col
          cols="12"
          lg="9"
          md="10"
          xl="6"
          class="mt-6"
        >
          <h3 class="text-h5 text-justify font-weight-bold">
            {{ $t("title.searchCarpool") }}
          </h3>
        </v-col>
      </v-row>
      <v-row
        v-if="isAccepted"
        justify="center"
      >
        <search
          :default-origin="selectedOrigin"
          :default-destination="selectedDestination"
          :geo-search-url="geodata.geocompleteuri"
          :user="user"
          :params="params"
          :punctual-date-optional="punctualDateOptional"
        />
      </v-row>

      <!--Confirmation Popup for LeaveCommunity-->
      <v-dialog
        v-model="leaveCommunityDialog"
        persistent
        max-width="500"
      >
        <v-card>
          <v-card-title class="text-h5">
            {{ $t("leaveCommunity.popup.title") }}
          </v-card-title>
          <v-card-text
            v-html="
              community.proposalsHidden
                ? $t('leaveCommunity.popup.content.isProposalsHidden')
                : $t('leaveCommunity.popup.content.isNotProposalsHidden')
            "
          />
          <v-card-actions>
            <v-spacer />
            <v-btn
              color="primary darken-1"
              text
              @click="leaveCommunityDialog = false"
            >
              {{ $t("no") }}
            </v-btn>
            <v-btn
              color="secondary darken-1"
              text
              @click="
                leaveCommunityDialog = false;
                postLeavingRequest();
              "
            >
              {{ $t("yes") }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>

      <!--Confirmation Popup for JoinCommunity-->
      <v-dialog
        v-model="joinCommunityDialog"
        persistent
        max-width="500"
      >
        <v-card>
          <v-card-title class="text-h5">
            {{ $t("joinCommunity.popup.title") }}
          </v-card-title>
          <v-card-text
            v-html="$t('joinCommunity.popup.content.printPhoneNumberWarning')"
          />
          <v-card-actions>
            <v-spacer />
            <v-btn
              color="primary darken-1"
              text
              @click="joinCommunityDialog = false"
            >
              {{ $t("no") }}
            </v-btn>
            <v-btn
              color="secondary darken-1"
              text
              @click="
                joinCommunityDialog = false;
                joinCommunity();
              "
            >
              {{ $t("yes") }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-container>
  </div>
</template>
<script>
import maxios from "@utils/maxios";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/community/Community/";
import CommunityMemberList from "@components/community/CommunityMemberList";
import CommunityInfos from "@components/community/CommunityInfos";
import Search from "@components/carpool/search/Search";
import CommunityLastUsers from "@components/community/CommunityLastUsers";
import MMap from "@components/utilities/MMap/MMap";
import L from "leaflet";

export default {
  components: {
    CommunityMemberList,
    CommunityInfos,
    Search,
    MMap,
    CommunityLastUsers,
  },
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    },
  },
  props: {
    user: {
      type: Object,
      default: null,
    },
    geodata: {
      type: Object,
      default: null,
    },
    geoSearchUrl: {
      type: String,
      default: ""
    },
    community: {
      type: Object,
      default: null,
    },
    lastUsers: {
      type: Object,
      default: null,
    },
    avatarVersion: {
      type: String,
      default: null,
    },
    urlAltAvatar: {
      type: String,
      default: null,
    },
    regular: {
      type: Boolean,
      default: false,
    },
    punctualDateOptional: {
      type: Boolean,
      default: false,
    },
    mapProvider: {
      type: String,
      default: "",
    },
    urlTiles: {
      type: String,
      default: "",
    },
    attributionCopyright: {
      type: String,
      default: "",
    },
    points: {
      type: Array,
      default: null,
    },
    userCommunityStatus: {
      type: Number,
      default: -1,
    },
    urlAdmin: {
      type: String,
      default: null,
    },
    canAccessAdminFromCommunity: {
      type: Boolean,
      default: false,
    },
    publishButtonAlwaysActive: {
      type: Boolean,
      default: false,
    },
    directMessage: {
      type: Boolean,
      default: false
    },
  },
  data() {
    return {
      search: "",
      headers: [
        {
          text: "Id",
          align: "left",
          sortable: false,
          value: "id",
        },
        { text: "Nom", value: "familyName" },
        { text: "Prenom", value: "givenName" },
      ],
      pointsToMap: [],
      relayPointsMap: [],
      directionWay: [],
      leaveCommunityDialog: false,
      joinCommunityDialog: false,
      loading: false,
      snackbar: false,
      textSnackbar: null,
      textSnackOk:
        this.community.validationType == 1
          ? this.$t("snackbar.joinCommunity.textOkManualValidation")
          : this.$t("snackbar.joinCommunity.textOkAutoValidation"),
      textSnackError: this.$t("snackbar.joinCommunity.textError"),
      errorUpdate: false,
      isAccepted: false,
      askToJoin: false,
      checkValidation: false,
      isLogged: false,
      domain: true,
      isSecured: this.community.isSecured,
      refreshMemberList: false,
      refreshLastUsers: false,
      params: { communityId: this.community.id },
      users: [],
      isCreator: false,
      selectedDestination: null,
      selectedOrigin: null
    };
  },
  computed:{
    canAdmin(){
      return this.isCreator || (this.userCommunityStatus==2)
    }
  },
  mounted() {
    if (this.userCommunityStatus >= 0) {
      this.isAccepted =
        this.userCommunityStatus == 1 || this.userCommunityStatus == 2;
      this.askToJoin = true;
    }

    //If the current user = creator : we show the btton for acces to admin
    if (this.user && this.community.user.id == this.user.id) {
      this.isCreator = true;
    }

    this.checkIfUserLogged();
    this.showCommunityProposals();
    this.checkDomain();
    this.getRelayPointsMap(); 
  },
  methods: {
    post: function(path, params, method = "post") {
      const form = document.createElement("form");
      form.method = method;
      form.action = window.location.origin + "/" + path;

      for (const key in params) {
        if (params.hasOwnProperty(key)) {
          const hiddenField = document.createElement("input");
          hiddenField.type = "hidden";
          hiddenField.name = key;
          hiddenField.value = params[key];
          form.appendChild(hiddenField);
        }
      }
      document.body.appendChild(form);
      form.submit();
    },
    getRelayPointsMap() {
      let params = {
        'communityId': this.community.id
      };
      maxios
        .post("/community/relay-point/map/",params)
        .then(res => {
          this.relayPointsMap = res.data;
          // console.log(res.data);
          this.showRelayPointsMap();
        })
        .catch(err => {
          console.error(err);
        });
    },
    showRelayPointsMap() {
      // add relay point address to display on the map
      if (this.relayPointsMap.length > 0) {
        this.relayPointsMap.forEach(relayPoint => {
          let icon = null;
          if(relayPoint.relayPointType){
            if(relayPoint.relayPointType.icon && relayPoint.relayPointType.icon.url !== ""){
              icon = relayPoint.relayPointType.icon.url;
            }
          }
          this.pointsToMap.push(this.buildRelayPoint(relayPoint.address.latitude,relayPoint.address.longitude,relayPoint.name,relayPoint.address,icon));
        });
      }
      this.$refs.mmap.redrawMap();
    },
    getCommunityUser() {
      if (this.user) {
        this.checkValidation = true;
        maxios
          .post(this.$t("urlCommunityUser"), {
            communityId: this.community.id,
            userId: this.user.id,
          })
          .then((res) => {
            if (res.data.length > 0) {
              //accepted as user or moderator
              this.isAccepted =
                res.data[0].status == 1 || res.data[0].status == 2;
              this.askToJoin = true;
            }
            this.checkValidation = false;
            this.loading = false;
          });
      } else {
        this.loading = false;
      }
    },
    joinCommunity() {
      this.loading = true;
      localStorage.setItem('gamificationInLocalStorage','1');
      maxios
        .post(this.$t("buttons.join.route", { id: this.community.id }), {
          headers: {
            "content-type": "application/json",
          },
        })
        .then((res) => {
          (res.data.id) ? this.errorUpdate = false : this.errorUpdate = true;
          this.askToJoin = true;
          this.isAccepted = false;
          this.snackbar = true;
          this.textSnackbar = this.errorUpdate
            ? this.$t("snackbar.joinCommunity.textError")
            : this.textSnackOk;
          this.refreshMemberList = true;
          this.refreshLastUsers = true;
          this.getCommunityUser();
          this.loading = false;
          location.reload();
        });
    },
    checkIfUserLogged() {
      if (this.user !== null) {
        this.isLogged = true;
      }
    },
    checkDomain() {
      if (this.community.validationType == 2) {
        let mailDomain = this.user.email.split("@")[1];
        if (!this.community.domain.includes(mailDomain)) {
          return (this.domain = false);
        }
      }
    },
    publish() {
      if (this.isLogged) {
        let lParams = {
          origin: null,
          destination: null,
          regular: this.regular,
          date: null,
          time: null,
          ...this.params,
        };
        this.post(`${this.$t("buttons.publish.route")}`, lParams);
      } else {
        window.location.href = this.$t("buttons.login.route");
      }
    },
    postLeavingRequest() {
      this.loading = true;
      maxios
        .post(this.$t("leaveCommunity.route", { id: this.community.id }), {
          headers: {
            "content-type": "application/json",
          },
        })
        .then((res) => {
          this.errorUpdate = res.data.state;
          this.askToJoin = false;
          this.isAccepted = false;
          this.textSnackbar = this.errorUpdate
            ? this.$t("snackbar.leaveCommunity.textError")
            : this.$t("snackbar.leaveCommunity.textOk");
          this.snackbar = true;
          this.refreshMemberList = true;
          this.refreshLastUsers = true;
          this.getCommunityUser();
          this.loading = false;
          location.reload(); // Yes, i know it's the lazy method to update the map...
        });
    },
    showCommunityProposals() {
      this.pointsToMap.length = 0;
      // add the community address to display on the map
      if (this.community.address && this.community.address.latitude !== null) {
        this.pointsToMap.push(
          this.buildPoint(
            this.community.address.latitude,
            this.community.address.longitude,
            this.community.name
          )
        );
      }

      // add all the waypoints of the community to display on the map
      // We draw straight lines between those points
      // if the user is already accepted or if the doesn't hide members or proposals to non members.
      if (
        this.isAccepted ||
        (!this.community.membersHidden && !this.community.proposalsHidden)
      ) {
        this.points.forEach((proposal, index) => {
          let currentProposal = { latLngs: [] };
          let infosForPopUp = {
            origin: "",
            destination: "",
            originLat: null,
            originLon: null,
            destinationLat: null,
            destinationLon: null,
            carpoolerFirstName: "",
            carpoolerLastName: "",
          };

          infosForPopUp.carpoolerFirstName = proposal.carpoolerFirstName;
          infosForPopUp.carpoolerLastName = proposal.carpoolerLastName;

          // We build the content of the popup
          currentProposal.desc =
            "<p style='text-align:center;'><strong>" +
            infosForPopUp.carpoolerFirstName +
            " " +
            infosForPopUp.carpoolerLastName +
            "</strong></p>";

          proposal.waypoints.forEach((waypoint, index) => {
            currentProposal.latLngs.push(waypoint.latLng);
            if (index == 0) {
              infosForPopUp.origin = waypoint.title;
              infosForPopUp.originLat = waypoint.latLng.lat;
              infosForPopUp.originLon = waypoint.latLng.lon;
            } else if (waypoint.destination) {
              infosForPopUp.destination = waypoint.title;
              infosForPopUp.destinationLat = waypoint.latLng.lat;
              infosForPopUp.destinationLon = waypoint.latLng.lon;
            }
            this.pointsToMap.push(
              this.buildPoint(
                waypoint.latLng.lat,
                waypoint.latLng.lon,
                currentProposal.desc,
                "",
                [],
                [],
                "<p>" + waypoint.title + "</p>"
              )
            );
          });

          currentProposal.desc +=
            "<p style='text-align:left;'><strong>" +
            this.$t("map.origin") +
            "</strong> : " +
            infosForPopUp.origin +
            "<br />";
          currentProposal.desc +=
            "<strong>" +
            this.$t("map.destination") +
            "</strong> : " +
            infosForPopUp.destination +
            "<br />";
          if (proposal.frequency == "regular")
            currentProposal.desc += "<em>" + this.$t("map.regular") + "</em>";

          // We add link to make the same search
          currentProposal.desc +=
            "<p><a href='" +
            proposal.searchLink +
            "'>" +
            this.$t("map.search.label") +
            "</a></p>";

          // And now the content of a tooltip (same as popup but without the button)
          currentProposal.title = currentProposal.desc;

          // We add the button to the popup (To Do: Button isn't functionnal. Find a good way to launch a research)
          //currentProposal.desc += "<br /><button type='button' class='v-btn v-btn--contained v-btn--rounded theme--light v-size--small secondary text-overline'>"+this.$t('map.findMatchings')+"</button>";

          // We are closing the two p
          currentProposal.title += "</p>";
          currentProposal.desc += "</p>";
          this.directionWay.push(currentProposal);
        });
      }
      this.$refs.mmap.redrawMap();
    },
    buildPoint: function(
      lat,
      lng,
      title = "",
      pictoUrl = "",
      size = [],
      anchor = [],
      popupDesc = ""
    ) {
      let point = {
        title: title,
        latLng: L.latLng(lat, lng),
        icon: {},
      };

      if (pictoUrl !== "") {
        point.icon = {
          url: pictoUrl,
          size: size,
          anchor: anchor,
        };
      }

      if (popupDesc !== "") {
        point.popup = {
          title: title,
          description: popupDesc,
        };
      }
      return point;
    },
    buildRelayPoint: function(
      lat,lng,title="",
      address="",
      icon=null
    ) {
      let point = {
        title:title,
        latLng:L.latLng(lat, lng),
        icon: {},
        address:address
      };

      if(icon){
        point.icon = {
          size:[36,42],
          url:icon
        }
      }
      return point;
    },
    selectedAsDestination(destination) {
      console.error(destination);
      this.selectedDestination = destination;
    },
    selectedAsOrigin(origin) {
      console.error(origin);
      this.selectedOrigin = origin;
    },
    contact: function(data) {
      const form = document.createElement("form");
      form.method = "post";
      form.action = this.$t("buttons.contact.route");

      const params = {
        carpool: 0,
        idRecipient: data.id,
        shortFamilyName: data.shortFamilyName,
        givenName: data.givenName,
        avatar: data.avatars[0],
      };

      for (const key in params) {
        if (params.hasOwnProperty(key)) {
          const hiddenField = document.createElement("input");
          hiddenField.type = "hidden";
          hiddenField.name = key;
          hiddenField.value = params[key];
          form.appendChild(hiddenField);
        }
      }
      document.body.appendChild(form);
      form.submit();
    },
    membersListRefreshed() {
      this.refreshMemberList = false;
    },
    lastUsersRefreshed() {
      this.refreshLastUsers = false;
    },
    searchMatchings() {
      console.error("searchMatchings");
    },
  },
};
</script>

<style lang="scss" scoped>
.multiline {
  padding: 20px;
  white-space: normal;
}
.vue2leaflet-map {
  z-index: 1;
}
</style>
