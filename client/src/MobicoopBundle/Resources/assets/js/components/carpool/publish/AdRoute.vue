<template>
  <v-container
    fluid
  >
    <!-- Origin -->
    <v-row
      align="center"
      no-gutters
    >
      <v-col
        cols="10"
        offset="1"
      >
        <geocomplete
          :uri="geoSearchUrl"
          :results-order="geoCompleteResultsOrder"
          :palette="geoCompletePalette"
          :chip="geoCompleteChip"
          :label="$t('origin.label')"
          required
          :address="initOrigin"
          @address-selected="originSelected"
        />
      </v-col>
    </v-row>

    <v-row
      v-show="!waypoints[3].visible"
      align="center"
      no-gutters
    >
      <v-col
        cols="10"
        offset="1"
        align="left"
      >
        <v-btn
          text
          icon
          @click="addWaypoint"
        >
          <v-icon>
            mdi-plus-circle-outline
          </v-icon>
        </v-btn>
        {{ $t('addWaypoint') }}
      </v-col>
    </v-row>

    <!-- Waypoints -->
    <!-- For now additional waypoints are hardcorded and limited to 4 -->
    <v-row
      v-for="(waypoint, index) in waypoints"
      v-show="waypoint.visible"
      :key="index"
      align="center"
      no-gutters
    >
      <v-col
        cols="10"
        offset="1"
      >
        <geocomplete
          :uri="geoSearchUrl"
          :results-order="geoCompleteResultsOrder"
          :palette="geoCompletePalette"
          :chip="geoCompleteChip"
          :label="$t('waypoint' + (index + 1) +'.label')"
          :address="waypoint.address"
          @address-selected="waypointSelected(index, ...arguments)"
        />
      </v-col>

      <v-col
        v-show="!waypoint.visible"
        cols="1"
      >
        <v-btn
          text
          icon
          @click="removeWaypoint(index)"
        >
          <v-icon>
            mdi-close-circle-outline
          </v-icon>
        </v-btn>
      </v-col>
    </v-row>

    <!-- destination -->
    <v-row
      align="center"
      no-gutters
    >
      <v-col
        cols="10"
        offset="1"
      >
        <geocomplete
          :uri="geoSearchUrl"
          :results-order="geoCompleteResultsOrder"
          :palette="geoCompletePalette"
          :chip="geoCompleteChip"
          :label="$t('destination.label')"
          required
          :address="initDestination"
          @address-selected="destinationSelected"
        />
      </v-col>
    </v-row>

    <!-- Avoid motorway -->
    <!-- <v-row
      align="center"
      justify="center"
      dense
    >
      <v-col
        cols="6"
      >
        <v-checkbox
          v-model="avoidMotorway"
          class="mt-0"
          :label="$t('avoidMotorway')"
          color="primary"
          hide-details
          @change="emitEvent"
        />
      </v-col>
    </v-row> -->

    <!-- Communities -->
    <v-row
      v-if="communities && communities.length > 0"
      align="center"
      justify="center"
      class="mt-2"
    >
      <v-col
        cols="10"
      >
        <v-autocomplete
          v-model="selectedCommunities"
          :items="communities"
          outlined
          chips
          :label="$t('communities.label')"
          item-text="name"
          item-value="id"
          multiple
          @change="emitEvent"
        >
          <template v-slot:selection="data">
            <v-chip
              v-bind="data.attrs"
              :input-value="data.selected"
              close
              @click="data.select"
              @click:close="removeCommunity(data.item)"
            >
              {{ data.item.name }}
            </v-chip>
          </template>
          <template v-slot:item="data">
            <template v-if="typeof data.item !== 'object'">
              <v-list-item-content v-text="data.item" />
            </template>
            <template v-else>
              <v-list-item-content>
                <v-list-item-title v-html="data.item.name" />
                <v-list-item-subtitle v-html="data.item.description" />
              </v-list-item-content>
            </template>
          </template>
        </v-autocomplete>
      </v-col>
    </v-row>


    <!-- Map (soon...) -->
    <v-row
      v-if="direction"
      align="center"
      justify="center"
    >
      <v-col
        cols="10"
      >
        <!-- Route detail -->
        <v-card>
          <v-row
            align="center"
            justify="space-around"
          >
            {{ $t('distance') }} : {{ direction.distance }} km
          </v-row>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import maxios from "@utils/maxios";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/carpool/publish/AdRoute/";
import Geocomplete from "@components/utilities/geography/Geocomplete";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    },
  },
  components: {
    Geocomplete
  },
  props: {
    geoSearchUrl: {
      type: String,
      default: ""
    },
    geoRouteUrl: {
      type: String,
      default: ""
    },
    user: {
      type: Object,
      default: null
    },
    initOrigin: {
      type: Object,
      default: null
    },
    initDestination: {
      type: Object,
      default: null
    },
    communityIds: {
      type: Array,
      default: null
    },
    initWaypoints: {
      type: Array,
      default: null
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
  },
  data() {
    return {
      origin: this.initOrigin,
      destination: this.initDestination,
      waypoints: [
        {
          visible: false,
          address: null
        },
        {
          visible: false,
          address: null
        },
        {
          visible: false,
          address: null
        },
        {
          visible: false,
          address: null
        },
      ],
      avoidMotorway: false,
      direction: null,
      selectedCommunities: this.communityIds,
      communities: null
    };
  },
  watch: {
    initOrigin() {
      this.origin = this.initOrigin;
      this.getRoute();
    },
    initDestination() {
      this.destination = this.initDestination;
      this.getRoute();
    },
    initWaypoints: {
      immediate: true,
      handler() {
        for (let i = 0; i < this.initWaypoints.length && i < 4; i ++) {
          this.waypoints[i].visible = true;
          this.waypoints[i].address = this.initWaypoints[i].address
        }
        this.getRoute();
      }
    }
  },
  mounted(){
    this.getRoute();
    this.getCommunities();
  },
  methods: {
    originSelected: function(address) {
      this.origin = address;
      this.getRoute();
    },
    destinationSelected: function(address) {
      this.destination = address;
      this.getRoute();
    },
    waypointSelected(id,address) {
      this.waypoints[id].address = address;
      this.getRoute();
    },
    getRoute() {
      if (this.origin != null && this.destination != null) {
        let params = `?points[0][longitude]=${this.origin.longitude}&points[0][latitude]=${this.origin.latitude}`;
        let nbWaypoints = 0;
        this.waypoints.forEach((item,key) => {
          if (item.visible && item.address) {
            nbWaypoints++;
            params += `&points[${nbWaypoints}][longitude]=${item.address.longitude}&points[${nbWaypoints}][latitude]=${item.address.latitude}`;
          }
        });
        nbWaypoints++;
        params += `&points[${nbWaypoints}][longitude]=${this.destination.longitude}&points[${nbWaypoints}][latitude]=${this.destination.latitude}`;
        maxios
          .get(`${this.geoRouteUrl}${params}`)
          .then(res => {
            this.direction = res.data.member[0];
            this.direction.distance = Math.ceil(this.direction.distance /1000)
            this.emitEvent();
          })
          .catch(err => {
            console.error(err);
            this.emitEvent();
          });
      } else {
        this.emitEvent();
      }
    },
    emitEvent: function() {
      this.$emit("change", {
        origin: this.origin,
        destination: this.destination,
        waypoints: this.waypoints,
        avoidMotorway: this.avoidMotorway,
        direction: this.direction,
        communities: this.selectedCommunities
      });
    },
    addWaypoint() {
      if (!this.waypoints[0].visible) {
        this.waypoints[0].visible = true;
      } else if (this.waypoints[0].visible && !this.waypoints[1].visible) {
        this.waypoints[1].visible = true;
      } else if (this.waypoints[0].visible && this.waypoints[1].visible && !this.waypoints[2].visible) {
        this.waypoints[2].visible = true;
      } else if (this.waypoints[0].visible && this.waypoints[1].visible && this.waypoints[2].visible && !this.waypoints[3].visible) {
        this.waypoints[3].visible = true;
      }
    },
    removeWaypoint(id) {
      this.waypoints[id].visible = false;
      this.waypoints[id].address = null;
      this.getRoute();
    },
    removeCommunity(item) {
      const index = this.selectedCommunities.indexOf(item.id);
      if (index >= 0) {
        this.selectedCommunities.splice(index, 1);
        this.emitEvent();
      }
    },
    getCommunities() {
      let params = {
        'userId':this.user.id
      }
      maxios.post(this.$t("communities.route"), params)
        .then(res => {
          this.communities = res.data;
        });
    }
  }
};
</script>
