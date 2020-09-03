import { addMinutes, subMinutes, format } from 'date-fns';

// Equivalent to moment.utc() for date-fns
// https://artemdemo.com/blog/20181104-replacing-momentjs/
// https://github.com/date-fns/date-fns/issues/556#issuecomment-391048347

export const toUTC = (date) => {
  const offset = date.getTimezoneOffset();
  return Math.sign(offset) !== -1 ? addMinutes(date, offset) : subMinutes(date, Math.abs(offset));
};

export const utcDateFormat = (dateString, pattern = "dd'/'MM'/'yyyy HH':'mm':'ss", ...rest) =>
  format(+toUTC(new Date(dateString)), pattern, ...rest);
