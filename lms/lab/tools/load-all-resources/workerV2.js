onmessage = async function (event) {
  if (!event.data) return;

  const numOfRequest = 50;

  try {
    let { folder, baseImageUrl, baseUrl } = event.data;
    const res = await this.fetch(`${baseUrl}/api.php?folder=${folder}`).then(
      (res) => res.json()
    );

    const { data } = res;
    const pages = Math.ceil(data.length / numOfRequest);

    this.postMessage({
      completed: 0,
      status: "pending",
      dataLength: data.length,
    });

    let totalCompleted = 0;
    let currentPage = 0;
    let promiseCall = [];

    while (currentPage < pages) {
      for (let i = 0; i < 3 && currentPage < pages; i++) {
        currentPage++;
        const p = new Promise((resolve) => {
          const childWorker = new Worker("download-worker.js");
          childWorker.onmessage = (event) => {
            const { completed } = event["data"];
            totalCompleted += completed;
            this.postMessage({
              completed: totalCompleted,
              status: currentPage == pages ? "done" : "processing",
              dataLength: data.length,
            });
            resolve();
          };

          childWorker.postMessage({
            data,
            itemPerPages: numOfRequest,
            page: currentPage,
            baseUrl: baseImageUrl,
          });
        });
        promiseCall.push(p);
      }
      await Promise.allSettled(promiseCall);
      promiseCall = [];
    }
  } catch (error) {
    this.postMessage({
      status: "error",
    });
    console.log(`Error from:::: worker ${error}`);
  }
};
