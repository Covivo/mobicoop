<template>
  <v-container fluid>
    <v-form v-if="!regular">
      <!-- Punctual -->
      <!-- First row -->
      <v-row
        align="center"
        justify="center"
        dense
      >
        <!-- Outward date -->
        <v-col
          cols="5"
          offset="2"
        >
          <v-menu
            v-model="menuOutwardDate"
            :close-on-content-click="false"
            transition="scale-transition"
            offset-y
            min-width="290px"
          >
            <template v-slot:activator="{ on }">
              <v-text-field
                :value="computedOutwardDateFormat"
                :label="$t('outwardDate.label')"
                readonly
                clearable
                v-on="on"
                @click:clear="clearOutwardDate"
              >
                <v-icon
                  slot="prepend"
                >
                  mdi-arrow-right-circle-outline
                </v-icon>
              </v-text-field>
            </template>
            <v-date-picker
              v-model="outwardDate"
              :locale="locale"
              no-title
              :min="nowDate"
              first-day-of-week="1"
              @input="menuOutwardDate = false"
              @change="changeDate()"
            />
          </v-menu>
        </v-col>

        <!-- Outward time -->
        <v-col
          cols="4"
        >
          <v-menu
            ref="menuOutwardTime"
            v-model="menuOutwardTime"
            :close-on-content-click="false"
            transition="scale-transition"
            offset-y
            max-width="290px"
            min-width="290px"
          >
            <template v-slot:activator="{ on }">
              <v-text-field
                id="outwardTime"
                v-model="outwardTime"
                :label="$t('outwardTime.label')"
                prepend-icon=""
                readonly
                clearable
                v-on="on"
                @click:clear="clearOutwardTime"
              />
            </template>
            <v-time-picker
              v-if="menuOutwardTime"
              v-model="outwardTime"
              format="24hr"
              :min="maxTimeIfToday"
              header-color="secondary"
              :allowed-minutes="allowedStep"
              @click:minute="changeTime()"
            />
          </v-menu>
        </v-col>
        <v-col
          cols="1"
        >
          <v-tooltip
            color="info"
            right
          >
            <template v-slot:activator="{ on }">
              <v-icon
                v-on="on"
              >
                mdi-help-circle-outline
              </v-icon>
            </template>
            <span>
              {{ infoMarginDuration }}</span>
          </v-tooltip>
        </v-col>
      </v-row>

      <!-- Second row -->
      <v-row
        align="center"
        dense
      >
        <!-- Return trip ? -->
        <v-col
          cols="2"
        >
          <v-checkbox
            v-model="returnTrip"
            class="mt-0"
            :label="$t('returnTrip.label')"
            color="primary"
            hide-details
            @change="checkReturnDesactivate($event)"
          />
        </v-col>

        <!-- Return date -->
        <v-col
          cols="5"
        >
          <v-menu
            v-model="menuReturnDate"
            :close-on-content-click="false"
            transition="scale-transition"
            offset-y
            min-width="290px"
          >
            <template v-slot:activator="{ on }">
              <v-text-field
                :value="computedReturnDateFormat"
                :label="$t('returnDate.label')"
                prepend-icon=""
                readonly
                clearable
                v-on="on"
                @click:clear="clearReturnDate"
              >
                <v-icon
                  slot="prepend"
                >
                  mdi-arrow-left-circle-outline
                </v-icon>
              </v-text-field>
            </template>
            <v-date-picker
              v-model="returnDate"
              :locale="locale"
              no-title
              first-day-of-week="1"
              :min="outwardDate"
              @input="menuReturnDate = false"
              @change="checkDateReturn($event)"
            />
          </v-menu>
        </v-col>

        <!-- Return time -->
        <v-col
          cols="4"
        >
          <v-menu
            ref="menuReturnTime"
            v-model="menuReturnTime"
            :close-on-content-click="false"
            :return-value.sync="returnTime"
            transition="scale-transition"
            offset-y
            max-width="290px"
            min-width="290px"
          >
            <template v-slot:activator="{ on }">
              <v-text-field
                v-model="returnTime"
                :label="$t('returnTime.label')"
                prepend-icon=""
                readonly
                clearable
                v-on="on"
                @click:clear="clearReturnTime"
              />
            </template>
            <v-time-picker
              v-if="menuReturnTime"
              v-model="returnTime"
              format="24hr"
              header-color="secondary"
              :min="minReturnTime"
              :allowed-minutes="allowedStep"
              @click:minute="checkDateReturn($event)"
            />
          </v-menu>
        </v-col>
        <v-col
          cols="1"
        >
          <v-tooltip
            color="info"
            right
          >
            <template v-slot:activator="{ on }">
              <v-icon
                v-on="on"
              >
                mdi-help-circle-outline
              </v-icon>
            </template>
            <span> {{ infoMarginDuration }}
            </span>
          </v-tooltip>
        </v-col>
      </v-row>
      <v-row v-if="returnTimeIsValid == false">
        <v-col>
          <p class="error--text">
            {{ $t('errorReturnTime') }}
          </p>
        </v-col>
      </v-row>
    </v-form>

    <!-- Regular -->
    <v-form v-else>
      <!-- we have a maximum of 7 different schedules, we iterate on them -->
      <v-row
        v-for="item in activeSchedules"
        :key="item.id"
        align="center"
        justify="center"
      >
        <v-col
          cols="10"
        >
          <!-- Schedule -->
          <v-card>
            <!-- Checkboxes for the days -->
            <v-row
              align="center"
              justify="space-around"
              dense
            >
              <v-checkbox
                v-model="item.mon"
                label="L"
                color="primary"
                :disabled="false"
                @change="getValueCheckbox($event,item,'mon')"
              />
              <v-checkbox
                v-model="item.tue"
                label="Ma"
                color="primary"
                @change="getValueCheckbox($event,item,'tue')"
              />
              <v-checkbox
                v-model="item.wed"
                label="Me"
                color="primary"
                @change="getValueCheckbox($event,item,'wed')"
              />
              <v-checkbox
                v-model="item.thu"
                label="J"
                color="primary"
                @change="getValueCheckbox($event,item,'thu')"
              />
              <v-checkbox
                v-model="item.fri"
                label="V"
                color="primary"
                @change="getValueCheckbox($event,item,'fri')"
              />
              <v-checkbox
                v-model="item.sat"
                label="S"
                color="primary"
                @change="getValueCheckbox($event,item,'sat')"
              />
              <v-checkbox
                v-model="item.sun"
                label="D"
                color="primary"
                @change="getValueCheckbox($event,item,'sun')"
              />
            </v-row>

            <!-- Times -->
            <v-row
              align="center"
              justify="space-around"
              dense
            >
              <!-- Outward time -->
              <v-col
                cols="5"
              >
                <v-menu
                  v-model="item.menuOutwardTime"
                  :close-on-content-click="false"
                  transition="scale-transition"
                  offset-y
                  max-width="290px"
                  min-width="290px"
                >
                  <template v-slot:activator="{ on }">
                    <v-text-field
                      v-model="item.outwardTime"
                      :hint="item.id > 0 ? $t('optional') : ''"
                      persistent-hint
                      :label="$t('regularOutwardTime.label')"
                      prepend-icon=""
                      readonly
                      v-on="on"
                      @blur="change"
                    >
                      <v-icon
                        slot="prepend"
                      >
                        mdi-arrow-right-circle
                      </v-icon>
                    </v-text-field>
                  </template>
                  <!--
                    we can't use $refs with v-for : https://vuejs.org/v2/guide/components-edge-cases.html#Accessing-Child-Component-Instances-amp-Child-Elements
                    because $refs are not reactive, we have to use a custom method closeOutwardTime() which will close the menu
                    -->
                  <v-time-picker
                    v-if="item.menuOutwardTime"
                    v-model="item.outwardTime"
                    format="24hr"
                    header-color="secondary"
                    :disabled="item.outwardDisabled"
                    :allowed-minutes="allowedStep"
                    @click:minute="closeOutwardTime(item.id)"
                    @change="blockTimeRegular($event,item.id)"
                  />
                </v-menu>
              </v-col>
              <v-col
                cols="1"
              >
                <v-tooltip
                  color="info"
                  right
                >
                  <template v-slot:activator="{ on }">
                    <v-icon
                      v-on="on"
                    >
                      mdi-help-circle-outline
                    </v-icon>
                  </template>
                  <span>{{ infoMarginDuration }}
                  </span>
                </v-tooltip>
              </v-col>

              <!-- Return time -->
              <v-col
                cols="5"
              >
                <v-menu
                  v-model="item.menuReturnTime"
                  :close-on-content-click="false"
                  transition="scale-transition"
                  offset-y
                  max-width="290px"
                  min-width="290px"
                >
                  <template v-slot:activator="{ on }">
                    <v-text-field
                      v-model="item.returnTime"
                      :label="$t('regularReturnTime.label')"
                      :hint="$t('optional')"
                      clearable
                      persistent-hint
                      prepend-icon=""
                      readonly
                      v-on="on"
                      @blur="change"
                    >
                      <v-icon
                        slot="prepend"
                      >
                        mdi-arrow-left-circle
                      </v-icon>
                    </v-text-field>
                  </template>
                  <!--
                    we can't use $refs with v-for : https://vuejs.org/v2/guide/components-edge-cases.html#Accessing-Child-Component-Instances-amp-Child-Elements
                    because $refs are not reactive, we have to use a custom method close() which will close the menu
                    -->
                  <v-time-picker
                    v-if="item.menuReturnTime"
                    v-model="item.returnTime"
                    format="24hr"
                    header-color="secondary"
                    :disabled="item.returnDisabled"
                    :min="item.minReturnTime ? item.minReturnTime : null"
                    :allowed-minutes="allowedStep"
                    @click:minute="closeReturnTime(item.id)"
                    @change="change()"
                  />
                </v-menu>
              </v-col>
              <v-col
                cols="1"
              >
                <v-tooltip
                  color="info"
                  right
                >
                  <template v-slot:activator="{ on }">
                    <v-icon
                      v-on="on"
                    >
                      mdi-help-circle-outline
                    </v-icon>
                  </template>
                  <span>{{ infoMarginDuration }}
                  </span>
                </v-tooltip>
              </v-col>
            </v-row>

            <!-- Remove schedule -->
            <v-row
              align="center"
              justify="end"
              dense
            >
              <v-col
                cols="2"
              >
                <v-btn
                  v-if="item.id>0"
                  text
                  icon
                  @click="removeSchedule(item.id)"
                >
                  <v-icon>
                    mdi-delete-circle
                  </v-icon>
                </v-btn>
              </v-col>
            </v-row>
          </v-card>
        </v-col>
      </v-row>

      <!-- Add schedule -->
      <v-row
        v-if="!schedules[6].visible"
        align="center"
        justify="center"
        dense
      >
        <v-col
          cols="10"
        >
          <v-btn
            text
            icon
            :disabled="!checkIfCurrentScheduleOk"
            @click="addSchedule"
          >
            <v-icon>
              mdi-plus-circle-outline
            </v-icon>
            {{ $t('addSchedule') }}
          </v-btn>
        </v-col>
      </v-row>
    </v-form>
  </v-container>
</template>

<script>
import moment from "moment";
import { isEmpty, remove, clone } from "lodash";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/carpool/publish/AdPlanification/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  components: {
  },
  props: {
    regular: {
      type: Boolean,
      default: false
    },
    initOutwardDate: {
      type: String,
      default: null
    },
    initOutwardTime: {
      type: String,
      default: null
    },
    initReturnDate: {
      type: String,
      default: null
    },
    initReturnTime: {
      type: String,
      default: null
    },
    defaultMarginDuration: {
      type: Number,
      default: null
    },
    route: {
      type: Object,
      default: null
    },
    initSchedule: {
      type: Object,
      default: null
    },
    defaultTimePrecision: {
      type: Number,
      default: null
    }
  },
  data() {
    return {
      outwardDate: this.initOutwardDate,
      outwardTime: moment(this.initOutwardTime).isValid() ? moment(this.initOutwardTime).format('HH:mm') : null,
      returnDate: this.initReturnDate,
      returnTime: moment(this.initReturnTime).isValid() ? moment(this.initReturnTime).format('HH:mm') : null,
      menuOutwardDate: false,
      menuOutwardTime: false,
      menuReturnDate: false,
      menuReturnTime: false,
      returnTrip: !!(this.initReturnDate && this.initReturnTime),
      marginDuration: this.defaultMarginDuration,
      locale: localStorage.getItem("X-LOCALE"),
      arrayDay : ['mon','tue','wed','thu','fri','sat','sun'],
      schedules: [],
      maxDateFromOutward : null,
      maxTimeFromOutward : null,
      maxTimeIfToday : null,
      nowDate : new Date().toISOString().slice(0,10),
      timePrecision: this.defaultTimePrecision,
    };
  },
  computed: {
    computedOutwardDateFormat() {
      return this.outwardDate
        ? moment(this.outwardDate).format(this.$t("fullDate"))
        : "";
    },
    computedReturnDateFormat() {
      return this.returnDate
        ? moment(this.returnDate).format(this.$t("fullDate"))
        : "";
    },
    activeSchedules() {
      return this.schedules.filter(function(schedule) {
        return schedule.visible;
      });
    },
    infoMarginDuration() {
      return this.$t("marginTooltip",{margin: this.marginDuration/60})
    },
    checkIfCurrentScheduleOk(){
      for (var s in this.activeSchedules){
        var i = this.activeSchedules[s];

        if ( !i.mon && !i.tue && !i.wed && !i.thu && !i.fri && !i.sat && !i.sun ) {
          return false;
        }
        if ( i.outwardTime == null && i.returnTime == null) return false;

      }
      return true;
    },
    returnTimeIsValid (){
      return moment(this.returnDate + ' ' + this.returnTime).isValid() && moment(this.outwardDate + ' ' + this.outwardTime) && !isEmpty(this.route)
        ? moment(this.returnDate + ' ' + this.returnTime) >= moment(this.outwardDate + ' ' + this.outwardTime).add(this.route.direction.duration, 'seconds')
        : null;
    },
    minReturnTime() {
      return this.returnDate === this.outwardDate
        ? moment(this.outwardDate + ' ' + this.outwardTime).add(this.route.direction.duration, 'seconds').format("HH:mm")
        : null;
    },
    allowedStep: function () {
      return m => m % this.timePrecision === 0
    }
  },
  watch: {
    initOutwardDate() {
      this.outwardDate = this.initOutwardDate;
    },
  },
  created:function(){
    // moment.locale(this.locale); // DEFINE DATE LANGUAGE
    this.setData();
  },

  methods: {
    change() {
      let validSchedules = JSON.parse(JSON.stringify(this.activeSchedules)); // little tweak to deep copy :)

      for (var i=0;i<validSchedules.length;i++) {

        if (!((validSchedules[i].mon || validSchedules[i].tue || validSchedules[i].wed || validSchedules[i].thu || validSchedules[i].fri || validSchedules[i].sat || validSchedules[i].sun) && (validSchedules[i].outwardTime || validSchedules[i].returnTime))) {
          validSchedules.splice(i);
        } else {
          delete validSchedules[i].id;
          delete validSchedules[i].visible;
          delete validSchedules[i].menuOutwardTime;
          delete validSchedules[i].menuReturnTime;
        }

      }
      this.$emit("change", {
        outwardDate: this.outwardDate,
        fullschedule : this.schedules,
        outwardTime: this.outwardTime,
        returnDate: this.returnDate,
        returnTime: this.returnTime,
        returnTrip: this.returnTrip,
        schedules: validSchedules,
        returnTimeIsValid: this.returnTimeIsValid,
      });
    },
    changeDate() {
      this.clearOtherFields();
      this.change();
    },
    changeTime() {
      this.$refs.menuOutwardTime.save(this.outwardTime);
      this.change();
    },
    checkDateReturn(e){
      this.returnTrip = !!e;
      this.$refs.menuReturnTime.save(this.returnTime);
      this.change();
    },
    clearOtherFields(){
      this.outwardTime = null;
      this.returnDate = null;
      this.returnTime = null;
    },
    blockTimeRegular(e,id){
      let timeSplitted = e.split(':');
      this.schedules[id]["minReturnTime"] = moment().hours(timeSplitted[0]).minutes(timeSplitted[1]).add(this.route.direction.duration, 'seconds').format("HH:mm");

      // test to allow return time to be set before outward time for regular work
      if(id !== 0 && this.schedules[id-1]['returnTime'] === null) {
        // console.error("");
      } else {
        this.schedules[id].maxTimeFromOutwardRegular = e;
      }
      this.change();
    },
    checkReturnDesactivate(e){
      if (!e) {
        this.returnDate = null;
        this.returnTime = null;
        this.returnTrip = false;
        this.change();
      }
    },
    getValueCheckbox(event,item,day){

      //If we have more than 1 day and checkbox is set to true
      if (this.activeSchedules.length > 1){
        var id = item.id;
        //We check a day
        //Patch : we un disabled the currents time for block selected, see TODO
        this.schedules[id].outwardDisabled = false;
        this.schedules[id].returnDisabled = false;

        if (event) {
          this.verifCurrentdDayInAllSchedules(day,id)
          // We uncheck a day
        }else{

          // TODO verif if all schedule have current day open, not just the currrent one
        }

      }
      this.change();
    },

    verifCurrentdDayInAllSchedules(day,currentSchedule){

      //Loop in active shcedules to find others day check
      for (var j in this.activeSchedules) {
        var c = this.activeSchedules[j];

        // Check if not active shcedule , then loop for check if other schedule have same day check
        if (c.id !== currentSchedule) {

          if (c.mon && day === 'mon') this.checkOutwardReturnAndDisabled(c);
          if (c.tue && day === 'tue') this.checkOutwardReturnAndDisabled(c);
          if (c.wed && day === 'wed') this.checkOutwardReturnAndDisabled(c);
          if (c.thu && day === 'thu') this.checkOutwardReturnAndDisabled(c);
          if (c.fri && day === 'fri') this.checkOutwardReturnAndDisabled(c);
          if (c.sat && day === 'sat') this.checkOutwardReturnAndDisabled(c);
          if (c.sun && day === 'sun') this.checkOutwardReturnAndDisabled(c);

        }
      }
    },
    checkOutwardReturnAndDisabled(c){
      if (c.outwardTime) {
        for (var k in this.activeSchedules) {
          var v = this.activeSchedules[k];
          if (v.id !== c.id) {
            this.activeSchedules[k].outwardDisabled = true;
            this.activeSchedules[k].outwardTime = null;
          }
        }
      }
      if (c.returnTime) {
        for (var l in this.activeSchedules) {
          var b = this.activeSchedules[l];
          if (b.id !== c.id) {
            this.activeSchedules[l].returnDisabled = true;
            this.activeSchedules[l].returnTime = null;
          }
        }
      }
    },
    //Fill array for verification time + date
    setData(){
      if (!isEmpty(this.initSchedule)) {
        let schedule = this.initSchedule;
        let tempSchedules = [];
        let days = clone(this.arrayDay);
        this.arrayDay.forEach(day => {
          if (schedule[day] === true) {
            tempSchedules.push({
              day: day,
              outwardTime: schedule[day + 'OutwardTime'],
              returnTime: schedule[day + 'ReturnTime']
            });
          }
        });

        let schedulesLength = tempSchedules.length;

        for (let i = 0; i < schedulesLength; i++) {
          if (!days.includes(tempSchedules[i].day)) continue;
          let tempDays = tempSchedules.filter(elem => {return elem.outwardTime === tempSchedules[i].outwardTime && elem.returnTime === tempSchedules[i].returnTime});

          this.schedules.push({
            id: i,
            visible: true,
            mon: tempDays.some(day => {return day.day === 'mon'}),
            tue: tempDays.some(day => {return day.day === 'tue'}),
            wed: tempDays.some(day => {return day.day === 'wed'}),
            thu: tempDays.some(day => {return day.day === 'thu'}),
            fri: tempDays.some(day => {return day.day === 'fri'}),
            sat: tempDays.some(day => {return day.day === 'sat'}),
            sun: tempDays.some(day => {return day.day === 'sun'}),
            outwardTime: moment(tempSchedules[i].outwardTime).isValid() ? moment(tempSchedules[i].outwardTime).utc().format("HH:mm") : null,
            returnTime: moment(tempSchedules[i].returnTime).isValid() ? moment(tempSchedules[i].returnTime).utc().format("HH:mm") : null,
            menuOutwardTime: false,
            menuReturnTime: false,
            outwardDisabled: false,
            returnDisabled: false,
            maxTimeFromOutwardRegular: null
          });

          tempDays.forEach(el => {
            remove(days, day => {
              return el.day === day;
            })
          })
        }
      }

      this.change();

      //Fill array schedules
      for (let j = this.schedules.length; j < 7; j++){
        this.schedules.push({
          id: j,
          visible: j === 0,
          mon: false,
          tue: false,
          wed: false,
          thu: false,
          fri: false,
          sat: false,
          sun: false,
          outwardTime: null,
          returnTime: null,
          menuOutwardTime: false,
          menuReturnTime: false,
          outwardDisabled : false,
          returnDisabled : false,
          maxTimeFromOutwardRegular : null
        });
      }

      this.change();
    },

    clearOutwardDate() {
      this.outwardDate = null;
      this.change();
    },
    clearOutwardTime() {
      this.outwardTime = null;
      this.change();
    },
    clearReturnDate() {
      this.returnDate = null;
      this.change();
    },
    clearReturnTime() {
      this.returnTime = null;
      this.change();
    },
    closeOutwardTime(id) {
      for (var i in this.schedules) {
        if (this.schedules[i].id == id) {
          this.schedules[i].menuOutwardTime = false;
          break;
        }
      }
    },
    closeReturnTime(id) {
      for (var i in this.schedules) {
        if (this.schedules[i].id == id) {
          this.schedules[i].menuReturnTime = false;
          break;
        }
      }
      this.change();
    },
    removeSchedule(id) {
      for (var i in this.schedules) {
        if (this.schedules[i].id == id) {
          this.schedules[i].visible = false;
          break;
        }
      }
      this.change();
    },
    addSchedule() {
      for (var i in this.schedules) {
        if (!this.schedules[i].visible) {
          this.schedules[i].visible = true;
          break;
        }
      }
      this.change();
    },
  }
};
</script>
