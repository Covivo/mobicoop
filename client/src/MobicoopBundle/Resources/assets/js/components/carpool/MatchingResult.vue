<template>
  <v-card
    outlined
  >
    <v-container>
      <v-row
        justify="start"
        align="center"
      >
        <!-- Role -->
        <v-col
          cols="auto"
          align="center"
        >
          <v-icon
            v-if="driver"
            color="primary"
            :size="!passenger ? '75' : '40'"
          >
            mdi-car
          </v-icon>

          <v-icon
            v-if="passenger"
            color="primary"
            :size="!driver ? '75' : '40'"
          >
            mdi-walk
          </v-icon>
        </v-col>

        <!-- Detail -->
        <v-col
          cols="10"
        >
          <!-- First line of detail -->
          <v-row
            align="center"
          >
            <!-- Time -->
            <v-col
              cols="3"
            >
              <v-list-item two-line>
                <v-list-item-content>
                  <v-list-item-title class="title font-weight-bold">
                    {{ computedTime }}
                  </v-list-item-title>
                  <v-list-item-subtitle class="subtitle-1">
                    {{ computedDate }}
                  </v-list-item-subtitle>
                </v-list-item-content>
              </v-list-item>
            </v-col>
            <!-- Route -->
            <v-col
              cols="6"
            >
              <route-summary
                :origin="computedOrigin"
                :destination="computedDestination"
                :type="2"
                :regular="regular"
              />
            </v-col>
            <!-- Seats -->
            <v-col
              cols="auto"
            >
              {{ $tc('places', computedSeats, { seats: computedSeats }) }}
            </v-col>
            <!-- Price -->
            <v-col
              cols="auto"
              class="title"
            >
              {{ computedPrice ? computedPrice +'€' : '' }}
            </v-col>
          </v-row>

          <v-divider /> 

          <!-- Second line of detail -->
          <v-row>
            <v-col
              cols="12"
            >
              Détail 2
            </v-col>
          </v-row>
        </v-col>
        
        <!-- display result-journey-detailed card - proposal Request and proposalOffer in array(matchings) order-->
      <!-- <result-journey-detailed-card
          :origin-latitude="originLatitude"
          :origin-longitude="originLongitude"
          :destination-latitude="destinationLatitude"
          :destination-longitude="destinationLongitude"
          :origin="origin"
          :destination="destination"
          :date="date"
          :carpool-results="carpoolResults"
          :matching-search-url="matchingSearchUrl"
          :matching="matching.proposalRequest"
        />
        <result-journey-detailed-card 
          :origin-latitude="originLatitude"
          :origin-longitude="originLongitude"
          :destination-latitude="destinationLatitude"
          :destination-longitude="destinationLongitude"
          :origin="origin"
          :destination="destination"
          :date="date"
          :carpool-results="carpoolResults"
          :matching-search-url="matchingSearchUrl"
          :matching="matching.proposalOffer"
          :regular="regular"
        />
        <v-row
          justify="center"
        >
          <v-divider class="divider-width" />
        </v-row> -->

      <!-- display result-user-detailed card - proposal Request and proposalOffer in array(matchings) order-->
      <!-- <result-user-detailed-card  
          :origin-latitude="originLatitude"
          :origin-longitude="originLongitude"
          :destination-latitude="destinationLatitude"
          :destination-longitude="destinationLongitude"
          :origin="origin"
          :destination="destination"
          :date="date"
          :carpool-results="carpoolResults"
          :matching-search-url="matchingSearchUrl"
          :matching="matching.proposalRequest" 
          :regular="regular"
        />   
        <v-row
          justify="center"
        />
        <result-user-detailed-card  
          :origin-latitude="originLatitude"
          :origin-longitude="originLongitude"
          :destination-latitude="destinationLatitude"
          :destination-longitude="destinationLongitude"
          :origin="origin"
          :destination="destination"
          :date="date"
          :carpool-results="carpoolResults"
          :matching-search-url="matchingSearchUrl"
          :matching="matching.proposalOffer" 
          :regular="regular"
        /> -->
      </v-row>
    </v-container>
  </v-card>
</template>

<script>
import { merge } from "lodash";
import moment from "moment";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/carpool/MatchingResult.json";
import TranslationsClient from "@clientTranslations/components/carpool/MatchingResult.json";
import RouteSummary from "../utilities/RouteSummary"

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  components: {
    RouteSummary
  },
  i18n: {
    messages: TranslationsMerged,
    sharedMessages: CommonTranslations
  },
  props: {
    matching: {
      type:Object,
      default: null
    },
    user: {
      type:Object,
      default: null
    },
    regular: {
      type: Boolean,
      default: false
    },
  },
  data : function() {
    return {
      locale: this.$i18n.locale,
    }
  },
  computed: {
    driver() {
      // a user is driver if he is the owner of the proposalOffer
      return this.matching.proposalOffer.user
    },
    passenger() {
      // a user is passenger if he is the owner of the proposalRequest or if he is also passenger for his proposalOffer
      return this.matching.proposalRequest.user || this.matching.proposalOffer.criteria.passenger
    },
    computedOrigin() {
      if (this.driver) {
        return {
          streetAddress: this.matching.proposalOffer.waypoints[0].address.streetAddress,
          addressLocality: this.matching.proposalOffer.waypoints[0].address.addressLocality
        }
      } else {
        return {
          streetAddress: this.matching.proposalRequest.waypoints[0].address.streetAddress,
          addressLocality: this.matching.proposalRequest.waypoints[0].address.addressLocality
        }
      }
    },
    computedDestination() {
      if (this.driver) {
        return {
          streetAddress: this.matching.proposalOffer.waypoints[1].address.streetAddress,
          addressLocality: this.matching.proposalOffer.waypoints[1].address.addressLocality,
        }
      } else {
        return {
          streetAddress: this.matching.proposalRequest.waypoints[1].address.streetAddress,
          addressLocality: this.matching.proposalRequest.waypoints[1].address.addressLocality,
        }
      }
    },
    computedTime() {
      moment.locale(this.locale);
      if (!this.regular && this.driver) {
        return this.matching.proposalOffer.criteria.fromTime
          ? moment(this.matching.proposalOffer.criteria.fromTime).format(this.$t("ui.i18n.time.format.hourMinute"))
          : ""; 
      } else if (!this.regular && this.passenger) {
        return this.matching.proposalRequest.criteria.fromTime
          ? moment(this.matching.proposalRequest.criteria.fromTime).format(this.$t("ui.i18n.time.format.hourMinute"))
          : ""; 
      }
      return "";
    },
    computedDate() {
      moment.locale(this.locale);
      if (!this.regular && this.driver) {
        return this.matching.proposalOffer.criteria.fromDate
          ? moment(this.matching.proposalOffer.criteria.fromDate).format(this.$t("ui.i18n.date.format.shortDate"))
          : ""; 
      } else if (!this.regular && this.passenger) {
        return this.matching.proposalRequest.criteria.fromDate
          ? moment(this.matching.proposalRequest.criteria.fromDate).format(this.$t("ui.i18n.date.format.shortDate"))
          : ""; 
      }
      return "";
    },
    computedSeats() {
      return this.driver ? this.matching.proposalOffer.criteria.seats : this.matching.proposalRequest.criteria.seats
    },
    computedPrice() {
      return this.driver ? Math.round((this.matching.proposalOffer.criteria.priceKm*this.matching.criteria.directionDriver.distance/1000)*100)/100 : null
    }
  },
  methods :{
  }
};
</script>
<style>
</style>