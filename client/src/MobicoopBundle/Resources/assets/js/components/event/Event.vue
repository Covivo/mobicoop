<template>
  <div>
    <!--SnackBar-->
    <v-snackbar
      v-model="snackbar"
      :color="errorUpdate ? 'error' : 'warning'"
      top
    >
      <!--      {{ (errorUpdate)?textSnackError:textSnackOk }}-->
      <v-btn
        color="white"
        text
        @click="snackbar = false"
      >
        <v-icon>mdi-close-circle-outline</v-icon>
      </v-btn>
    </v-snackbar>

    <v-container>
      <!-- event buttons and map -->
      <v-row justify="center">
        <v-col
          cols="12"
          lg="9"
          md="10"
          xl="6"
          class="text-justify"
        >
          <!-- event : avatar, title and description -->
          <event-infos
            :event="event"
            :url-alt-avatar="urlAltAvatar"
          />
          <!-- event buttons and map -->
          <v-row class="text-center">
            <v-col
              cols="4"
              class="text-center"
            >
              <!-- button  -->
              <div>
                <v-btn
                  v-if="!eventPassed"
                  color="secondary"
                  rounded
                  :loading="loading"
                  @click="publish"
                >
                  {{ $t("buttons.publish.label") }}
                </v-btn>
                <v-btn
                  v-if="eventWidget"
                  class="mt-3"
                  color="primary"
                  rounded
                  :loading="loading"
                  :href="$t('buttons.widget.route', {id: event.id, urlKey: event.urlKey})"
                >
                  {{ $t("buttons.widget.label") }}
                </v-btn>
                <br>
                <v-btn
                  v-if="user && event.creatorId == user.id"
                  class="mt-3"
                  color="warning"
                  rounded
                  :loading="loading"
                  :href="$t('buttons.edit.route', { id: event.id })"
                >
                  {{ $t("buttons.edit.label") }}
                </v-btn>
                <br>
                <v-btn
                  v-if="user && event.creatorId == user.id"
                  class="mt-3"
                  color="error"
                  :loading="loading"
                  rounded
                  @click="deleteEvent"
                >
                  {{ $t("buttons.delete.label") }}
                </v-btn>
                <report
                  v-if="!user || event.creatorId !== user.id"
                  class="mt-3"
                  :event="event"
                />
              </div>
            </v-col>
            <!-- map -->
            <v-col cols="8">
              <v-card
                v-show="loadingMap"
                flat
                class="text-center"
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
                :points="pointsToMap"
                :ways="directionWay"
                :provider="mapProvider"
                :url-tiles="urlTiles"
                :attribution-copyright="attributionCopyright"
              />
            </v-col>
          </v-row>
        </v-col>
      </v-row>
      <!-- search journey -->
      <v-row
        v-if="!eventPassed"
        justify="center"
      >
        <v-col
          cols="12"
          lg="9"
          md="10"
          xl="6"
          class="text-center mt-6"
        >
          <h3 class="text-h5 text-justify font-weight-bold">
            {{ $t("title.searchCarpool") }}
          </h3>
        </v-col>
      </v-row>
      <v-row
        v-if="!eventPassed"
        class="text-center"
        justify="center"
      >
        <search
          :geo-search-url="geodata.geocompleteuri"
          :geo-complete-results-order="geoCompleteResultsOrder"
          :geo-complete-palette="geoCompletePalette"
          :geo-complete-chip="geoCompleteChip"
          :user="user"
          :params="params"
          :punctual-date-optional="punctualDateOptional"
          :regular="regular"
          :default-destination="defaultDestination"
          :publish-button-always-active="publishButtonAlwaysActive"
          :default-outward-date="eventFormatedDate"
          :date-time-picker="dateTimePicker"
        />
      </v-row>
    </v-container>
    <LoginOrRegisterFirst
      :id="lEventId"
      :show-dialog="loginOrRegisterDialog"
      type="event"
      @closeLoginOrRegisterDialog="loginOrRegisterDialog = false"
    />
  </div>
</template>
<script>
import {
  messages_en,
  messages_fr,
  messages_eu,
  messages_nl
} from "@translations/components/event/Event/";
import EventInfos from "@components/event/EventInfos";
import Report from "@components/utilities/Report";
import Search from "@components/carpool/search/Search";
import LoginOrRegisterFirst from "@components/utilities/LoginOrRegisterFirst";
import MMap from "@components/utilities/MMap/MMap";
import L from "leaflet";
import moment from "moment";
import maxios from "@utils/maxios";

const DATE_FORMAT = "YYYY-MM-DD";

export default {
  components: {
    Report,
    EventInfos,
    Search,
    MMap,
    LoginOrRegisterFirst
  },
  i18n: {
    messages: {
      en: messages_en,
      nl: messages_nl,
      fr: messages_fr,
      eu: messages_eu
    }
  },
  props: {
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
    event: {
      type: Object,
      default: null
    },
    lastUsers: {
      type: Array,
      default: null
    },
    urlAltAvatar: {
      type: String,
      default: null
    },
    punctualDateOptional: {
      type: Boolean,
      default: false
    },
    mapProvider: {
      type: String,
      default: ""
    },
    urlTiles: {
      type: String,
      default: ""
    },
    attributionCopyright: {
      type: String,
      default: ""
    },
    initDestination: {
      type: Object,
      default: null
    },
    initOrigin: {
      type: Object,
      default: null
    },
    points: {
      type: Array,
      default: null
    },
    publishButtonAlwaysActive: {
      type: Boolean,
      default: false
    },
    geoCompleteResultsOrder: {
      type: Array,
      default: null
    },
    geoCompletePalette: {
      type: Object,
      default: () => ({})
    },
    geoCompleteChip: {
      type: Boolean,
      default: false
    },
    dateTimePicker: {
      type: Boolean,
      default: false
    },
    eventWidget: {
      type: Boolean,
      default: false
    },
  },
  data() {
    return {
      locale: localStorage.getItem("X-LOCALE"),
      destination: "",
      origin: this.initOrigin,
      search: "",
      pointsToMap: [],
      directionWay: [],
      loading: false,
      snackbar: false,
      errorUpdate: false,
      isLogged: false,
      loadingMap: false,
      params: { eventId: this.event.id },
      defaultDestination: this.initDestination,
      regular: false,
      eventPassed: false,
      loginOrRegisterDialog: false,
      lEventId: this.event.id ? this.event.id : null,
      lParams: {},
      date: this.event.fromDate.date
    };
  },
  computed: {
    dateFormated() {
      return this.date ? moment.utc(this.date, "YYYY-MM-DD HH:mm:ss.SSSSSS").format(DATE_FORMAT) : "";
    },
    eventFormatedDate() {               // If the event start date has passed, then the current date is displayed
      const now = new Date();

      return now > new Date(this.date) ? moment.utc(now).format(DATE_FORMAT) : this.dateFormated;
    }
    // Link the event in the adresse
  },
  created: function() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
    this.$set(this.initDestination, "event", this.event);
    this.destination = this.initDestination;
  },
  mounted() {
    this.showEventProposals();
    this.checkIfEventIsPassed();
    this.checkIfUserLogged();
  },
  methods: {
    searchChanged: function(search) {
      this.origin = search.origin;
      this.destination = search.destination;
      this.dataRegular = search.regular;
      this.date = search.date;
    },
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
    checkIfUserLogged() {
      if (this.user !== null) {
        this.isLogged = true;
      }
    },
    checkDomain() {
      if (this.event.validationType == 2) {
        let mailDomain = this.user.email.split("@")[1];
        if (!this.event.domain.includes(mailDomain)) {
          return (this.domain = false);
        }
      }
    },

    publish() {
      this.lParams = {
        origin: null,
        destination: JSON.stringify(this.destination),
        regular: null,
        date: this.dateFormated,
        time: null,
        ...this.params
      };
      if (this.isLogged) {
        this.post(
          `${this.$t("buttons.publish.route", { id: this.lEventId })}`,
          this.lParams
        );
      } else {
        localStorage.setItem('adSettings', JSON.stringify(this.lParams));
        this.loginOrRegister();
      }
    },

    deleteEvent() {
      this.loading = true;
      maxios
        .delete(this.$t("delete.route"), {
          data: {
            eventId: this.event.id
          }
        })
        .then(function(response) {
          window.location.href="/"
        })
        .catch(function(error) {
          self.alert = {
            type: "error",
            message: self.$t("delete.error")
          };
        })

    },

    showEventProposals() {
      this.pointsToMap.length = 0;
      // add the event address to display on the map
      if (this.event.address) {
        this.pointsToMap.push(
          this.buildPoint(
            this.event.address.latitude,
            this.event.address.longitude,
            this.event.name,
            "/images/cartography/pictos/destination.png",
            [36, 42]
          )
        );
      }

      // add all the waypoints of the event to display on the map
      // We draw straight lines between those points
      // if the user is already accepted or if the doesn't hide members or proposals to non members.
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
          carpoolerLastName: ""
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
        // get the origin waypoint (first)
        infosForPopUp.origin = proposal.waypoints[0].title;
        infosForPopUp.originLat = proposal.waypoints[0].latLng.lat;
        infosForPopUp.originLon = proposal.waypoints[0].latLng.lon;

        currentProposal.desc +=
          "<p style='text-align:left;'>" +
          this.$t("map.origin") +
          "</strong> : " +
          infosForPopUp.origin +
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
        // We are closing the two p;
        currentProposal.desc += "</p>";

        // And now the content of a tooltip (same as popup but without the button)
        currentProposal.title = currentProposal.desc;

        // We set the destination before the push to directinWay. It's the address of the event
        let destination = {
          lat: this.event.address.latitude,
          lon: this.event.address.longitude
        };
        currentProposal.latLngs.push(destination);

        this.directionWay.push(currentProposal);
        proposal.waypoints.forEach((waypoint, index) => {
          currentProposal.latLngs.push(waypoint.latLng);
          this.pointsToMap.push(
            this.buildPoint(
              waypoint.latLng.lat,
              waypoint.latLng.lon,
              waypoint.title,
              "",
              [],
              [],
              "<p>" + currentProposal.desc + "</p>"
            )
          );
        });
      });
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
        icon: {}
      };

      if (pictoUrl !== "") {
        point.icon = {
          url: pictoUrl,
          size: size,
          anchor: anchor
        };
      }

      if (popupDesc !== "") {
        point.popup = {
          title: title,
          description: popupDesc
        };
      }
      return point;
    },

    checkIfEventIsPassed() {
      let now = moment();
      if (now > moment(this.event.toDate.date)) {
        this.eventPassed = true;
      }
    },
    loginOrRegister() {
      this.loginOrRegisterDialog = true;
    }
  }
};
</script>
