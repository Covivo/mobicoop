import * as React from 'react';
import { FC } from 'react';
import { Card, CardHeader, CardContent } from '@material-ui/core';
import { ResponsiveContainer, BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip } from 'recharts';
import { useTranslate } from 'react-admin';

const lastMonthDays = (users) => {
  const today = new Date(new Date().toDateString()).getTime();
  const lastDay =
    users && users.length
      ? new Date(users[users.length - 1].createdDate.slice(0, 10)).getTime()
      : today;
  const oneDay = 24 * 60 * 60 * 1000;
  const length = Math.max(30, Math.ceil((today - lastDay) / oneDay));

  return Array.from({ length }, (_, i) => today - i * oneDay).reverse();
};

const aMonthAgo = new Date();

aMonthAgo.setDate(aMonthAgo.getDate() - 30);

const dateFormatter = (date) => new Date(date).toLocaleDateString();

const aggregateByDay = (users) =>
  users
    .filter((user) => !user.unsubscribeDate)
    .reduce((acc, curr) => {
      const day = new Date(new Date(curr.createdDate.slice(0, 10)).toDateString()).getTime();
      if (!acc[day]) {
        acc[day] = 0;
      }
      acc[day] += 1;
      return acc;
    }, {});

const getUsersByDay = (users) => {
  const daysWithUsers = aggregateByDay(users);
  return lastMonthDays(users).map((date) => ({
    date,
    total: daysWithUsers[date] || 0,
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
      <CardHeader title="Historique des dernières inscriptions" />
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
