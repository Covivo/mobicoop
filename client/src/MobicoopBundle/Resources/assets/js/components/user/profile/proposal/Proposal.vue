<template>
  <v-container>
    <v-card>
      <v-container class="py-0">
        <v-row
          class="primary darken-2"
        >
          <v-icon
            v-if="isDriver"
            class="accent darken-2 pa-1 px-3 white--text"
          >
            mdi-car
          </v-icon>
          <v-divider
            v-if="isDriver && isPassenger"
            vertical
          />
          <v-icon
            v-if="isPassenger"
            class="secondary darken-2 pa-1 px-3 white--text"
          >
            mdi-walk
          </v-icon>
          <v-spacer />
          <v-btn
            class="secondary my-1"
            icon
          >
            <v-icon
              class="white--text"
            >
              mdi-delete-outline
            </v-icon>
          </v-btn>
          <v-btn
            class="secondary ma-1"
            icon
          >
            <v-icon class="white--text">
              mdi-pencil
            </v-icon>
          </v-btn>
          <v-btn
            class="secondary my-1 mr-1"
            icon
          >
            <v-icon class="white--text">
              mdi-pause
            </v-icon>
          </v-btn>
        </v-row>
      </v-container>
      
      <v-card-text v-if="isRegular">
        <v-container
          fluid
        >
          <v-row>
            <v-col cols="5">
              <regular-days-summary
                :mon-active="proposal.outward.criteria.monCheck"
                :tue-active="proposal.outward.criteria.tueCheck"
                :wed-active="proposal.outward.criteria.wedCheck"
                :thu-active="proposal.outward.criteria.thuCheck"
                :fri-active="proposal.outward.criteria.friCheck"
                :sat-active="proposal.outward.criteria.satCheck"
                :sun-active="proposal.outward.criteria.sunCheck"
                :date-end-of-validity="proposal.outward.criteria.toDate"
              />
            </v-col>
            <v-col>
              <span class="accent--text text--darken-2 font-weight-bold">{{ $t('outward') }}</span>
              
              <v-icon class="accent--text text--darken-2 font-weight-bold">
                mdi-arrow-right
              </v-icon>
              
              <span class="primary--text text--darken-3 body-1">
                {{ formatTime(proposal.outward.criteria.monTime) }}
              </span>
            </v-col>

            <!-- Return -->
            <v-col
              v-if="hasReturn"
            >
              <span class="accent--text text--darken-2 font-weight-bold">{{ $t('return') }}</span>

              <v-icon class="accent--text text--darken-2 font-weight-bold">
                mdi-arrow-left
              </v-icon>

              <span class="primary--text text--darken-3 body-1">
                {{ formatTime(proposal.return.criteria.monTime) }}
              </span>
            </v-col>
          </v-row>
          <v-row justify="center">
            <v-col
              cols="12"
              class="primary darken-4"
            >
              <v-container class="pa-0">
                <v-row>
                  <v-col
                    cols="6"
                    class="py-0"
                  >
                    <route-summary
                      :origin="proposal.outward.waypoints[0].address"
                      :destination="proposal.outward.waypoints[proposal.outward.waypoints.length - 1].address"
                      :type="proposal.outward.criteria.frequency"
                      :regular="isRegular"
                      text-color-class="white--text"
                      icon-color="accent"
                    />
                  </v-col>
                </v-row>
              </v-container>
            </v-col>
          </v-row>
        </v-container>
      </v-card-text>
      
      <v-card-text v-else>
        non regular
      </v-card-text>

      <v-divider />
      
      <v-card-actions class="py-0">
        <v-container fluid>
          <v-row>
            <v-col cols="3">
              {{ proposal.outward.criteria.seats }} places
            </v-col>
            <v-col cols="3">
              {{ proposal.outward.criteria.price }} €
            </v-col>
            <v-col cols="6">
              <v-btn
                icon
              >
                <v-icon class="primary--text">
                  mdi-email
                </v-icon>
              </v-btn>
              <v-btn
                color="success"
                rounded
              >
                {{ proposal.outward.matchingRequests.length }} covoitureurs potentiels
              </v-btn>
            </v-col>
          </v-row>
        </v-container>
      </v-card-actions>
    </v-card>
  </v-container>
</template>

<script>
import moment from "moment";
import RegularDaysSummary from '@components/carpool/utilities/RegularDaysSummary.vue';
import RouteSummary from '@components/carpool/utilities/RouteSummary.vue';
  
export default {
  components: {
    RegularDaysSummary,
    RouteSummary
  },
  props: {
    proposal: {
      type: Object,
      default: () => {}
    }
  },
  data () {
    return {
      
    }
  },
  computed: {
    isDriver () {
      return !!this.proposal.outward.criteria.driver;
    },
    isPassenger () {
      return !!this.proposal.outward.criteria.passenger;
    },
    isRegular () {
      return this.proposal.outward.criteria.frequency === 2;
    },
    hasReturn () {
      return this.proposal.return;
    },
    activeOutwardWaypoints() {
      if (this.proposal.outward && this.proposal.outward.waypoints) {
        return this.proposal.outward.waypoints.filter(function(waypoint) {
          return waypoint.visible && waypoint.address;
        });
      }
      return null;
    },
  },
  methods: {
    formatTime(time) {
      moment.locale(this.locale);
      return moment(time).format(this.$t("ui.i18n.time.format.hourMinute"));
    }
  }
}
</script>

<style scoped lang="scss">
</style>