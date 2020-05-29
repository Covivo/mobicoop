import { format } from 'date-fns';

const hourRange = (start, end) => {
  const s = start && format(new Date(start), "HH'h'mm").replace('h00', 'h');
  const e = end && format(new Date(end), "HH'h'mm").replace('h00', 'h');

  return s && e && `${s}-${e}`;
};

export const resolveVoluntaryAvailabilityHourRanges = ({
  mMinTime,
  mMaxTime,
  aMinTime,
  aMaxTime,
  eMinTime,
  eMaxTime,
}) => ({
  morning: hourRange(mMinTime, mMaxTime),
  afternoon: hourRange(aMinTime, aMaxTime),
  evening: hourRange(eMinTime, eMaxTime),
});
