import * as React from 'react';
import { Card, CardHeader, CardContent } from '@material-ui/core';
import { ResponsiveContainer, BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip } from 'recharts';
import { useTranslate } from 'react-admin';

const addDays = (startDate, days) => {
  const date = new Date(startDate.valueOf());
  date.setDate(date.getDate() + days);
  return date.valueOf();
};

const lastMonthDays = (users) => {
  const today = new Date(new Date().toDateString()).getTime();
  const lastDay =
    users && users.length
      ? new Date(users[users.length - 1].createdDate.slice(0, 10)).getTime()
      : today;

  const returnedArray = [];
  for (let currentDay = lastDay; currentDay <= today; currentDay = addDays(currentDay, 1)) {
    returnedArray.push(currentDay);
  }

  return returnedArray.slice(-40);
};

const aMonthAgo = new Date();

aMonthAgo.setDate(aMonthAgo.getDate() - 30);

const dateFormatter = (date) => new Date(date).toLocaleDateString();

const isSameDate = (date1, date2) => {
  const date1ToDate = new Date(date1);
  const date2ToDate = new Date(date2);
  return (
    date1ToDate.getDate() === date2ToDate.getDate() &&
    date1ToDate.getMonth() === date2ToDate.getMonth() &&
    date1ToDate.getFullYear() === date2ToDate.getFullYear()
  );
};

const findNbUsersByDate = (users, matchingDate) => {
  const matchingUsers = users
    .filter((user) => !user.unsubscribeDate)
    .filter((user) => isSameDate(user.createdDate.slice(0, 10), matchingDate));
  return matchingUsers.length;
};

const getUsersByDay = (users) => {
  return lastMonthDays(users).map((date) => ({
    date,
    total: findNbUsersByDate(users, date),
  }));
};

const UsersChart = ({ users }) => {
  const translate = useTranslate();
  if (!users || !users.length) return null;

  const data = getUsersByDay(users);

  const domain = [
    data && data[0] && data[0].date ? data[0].date : new Date(new Date().toDateString()).getTime(),
    new Date(new Date().toDateString()).getTime(),
  ];
  const lastDay =
    users && users.length
      ? new Date(users[users.length - 1]).getTime()
      : new Date(new Date().toDateString()).getTime();

  return (
    <Card>
      <CardHeader title="Historique des inscriptions récentes" />
      <CardContent>
        <ResponsiveContainer width="100%" height={300}>
          <BarChart data={data}>
            <XAxis
              dataKey="date"
              name="Date"
              type="number"
              scale="time"
              domain={domain}
              tickFormatter={dateFormatter}
            />
            <YAxis dataKey="total" name="Nb" />
            <CartesianGrid strokeDasharray="3 3" />

            <Bar dataKey="total" fill="#31708f" />
          </BarChart>
        </ResponsiveContainer>
      </CardContent>
    </Card>
  );
};

export default UsersChart;
